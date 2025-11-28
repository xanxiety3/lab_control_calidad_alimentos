<?php

namespace App\Http\Controllers;

use App\Mail\ReporteLaboratorioMail;
use App\Mail\ResultadoMailable;
use App\Models\EnsayoFisicoquimico;
use App\Models\EnsayoMicrobiologia;
use App\Models\Muestra;
use App\Models\MuestraEnsayo;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ResultadoController extends Controller
{

    //Dilucion 1: titulo
    //C1: {valor}
    //C2 {valor}
    //Dilucion 2: titulo
    //C1: {valor}
    //C2 {valor}
    //boton (calcular) -> evento -> logica= la sumatoria de dilucion 1 y dilucion 2 entre 4 * mil = resultado
    //resultado se vaa a expresar en ufc/gr si es queso, ufc/ml si es leche.
    //etiqeta donde se refleje el valor de {resultado}
    //ESTO SOLO APLICA PARA MICROBIOLOGIA(MOHOS, LEVADURA, COLIFORMES, E.COLI, STAFILO COCCUS, MESOFILOS)

    //GRASA
    //replica 1 
    //menisco superior (B) {valor}
    //menisco inferior (A) {valor}


    //replica 2
    //menisco superior (B) {valor}
    //menisco inferior (A) {valor}

    //boton(calcular) -> la resta de replica 1 y replica 2, el resultado de los dos, se suman y luego se dividen entre dos(promedio)g/100g y leche ml/100ml





    //SOLIDOS TOTALES y HUMEDAD

    // Replica 1 titulo
    // m0: {valor}
    // m1: {valor}
    // m2: {valor}

    // boton (calcular) se resta m2-m0 y el resultado se divide entre m1-m0 *100


    // agregar campoo firma
    // cambiar los campos de ensayos


    public function show($id)
    {
        $solicitud = Solicitud::with([
            'cliente.persona',
            'muestras.tipoMuestra',
            'muestras.muestraEnsayos.ensayo',
            'muestras.muestraEnsayos.microbiologia',
            'muestras.muestraEnsayos.fisicoquimico'
        ])->findOrFail($id);

        $gestores = User::whereHas('role', function ($q) {
            $q->where('role_id', 4);
        })->get();

        return view('resultados.show', compact('solicitud', 'gestores'));
    }





    public function index()
    {
        // Cargar las relaciones necesarias
        $solicitudes = Solicitud::with([
            'cliente',
            'muestras.muestraEnsayos'
        ])->latest()->get();

        // Contadores para los filtros
        $contadores = [
            'todas' => $solicitudes->count(),
            'pendiente' => $solicitudes->filter(function ($solicitud) {
                return $solicitud->estado_global === 'pendiente';
            })->count(),
            'en_proceso' => $solicitudes->filter(function ($solicitud) {
                return $solicitud->estado_global === 'en_proceso';
            })->count(),
            'finalizada' => $solicitudes->filter(function ($solicitud) {
                return $solicitud->estado_global === 'finalizada';
            })->count(),
        ];

        return view('dashboard.analista', compact('solicitudes', 'contadores'));
    }




    public function edit($id)
    {
        $solicitud = Solicitud::with([
            'cliente',
            'muestras.tipoMuestra',
            'muestras.muestraEnsayos.ensayo'
        ])->findOrFail($id);

        // Filtrar en PHP (más simple y evita problemas de consultas complejas)
        $solicitud->muestras = $solicitud->muestras->map(function ($muestra) {
            $muestra->muestraEnsayos = $muestra->muestraEnsayos->filter(function ($muestraEnsayo) {
                return empty($muestraEnsayo->resultado) || $muestraEnsayo->resultado == 0;
            });
            return $muestra;
        })->filter(function ($muestra) {
            return $muestra->muestraEnsayos->count() > 0;
        });

        return view('resultados.edit', compact('solicitud'));
    }

    public function update(Request $request, $solicitudId)
    {
        try {

            // Obtener todos los campos enviados
            $data = $request->all();

            foreach ($data as $key => $value) {

                // Filtrar solo los campos que empiezan en "resultado_"
                if (str_starts_with($key, 'resultado_')) {

                    // Extraer el ID
                    $id = str_replace('resultado_', '', $key);

                    $resultado = $value;
                    $observaciones = $data["observaciones_$id"] ?? null;

                    // Buscar el modelo
                    $me = MuestraEnsayo::find($id);

                    if (!$me) {
                        continue;
                    }

                    // Actualizar
                    $me->update([
                        'resultado'           => $resultado,
                        'observaciones'       => $observaciones,
                        'fecha_analisis'      => now(),
                        'estado'              => 'aceptado',
                        'unidad_medida'       => $me->ensayo->unidad_medida ?? null,
                        'codigo_trazabilidad' => $me->muestra->codigo_interno ?? null,
                    ]);
                }
            }

            return redirect()
                ->route('resultados.index')
                ->with('success', 'Resultados guardados correctamente.');
        } catch (\Exception $e) {
            dd($e->getMessage(), $e->getLine(), $e->getFile());
        }
    }




    public function exportarDatos($id)
    {
        $solicitud = Solicitud::with([
            'cliente.persona',
            'muestras.tipoMuestra',
            'muestras.ensayos'
        ])->findOrFail($id);

        if ($solicitud->muestras->isEmpty()) {
            return back()->with('error', 'No hay muestras registradas para esta solicitud.');
        }

        $archivosGenerados = [];

        foreach ($solicitud->muestras as $muestra) {

            $tipo = strtolower($muestra->tipoMuestra->nombre ?? '');

            if (str_contains($tipo, 'leche')) {
                $template = 'datos-leche.xlsx';
                $filaDatos = 11;
                $mapaColumnas = [
                    'aerobios' => 'F',
                    'grasa' => 'G',
                    'solidos totales' => 'H',
                    'densidad' => 'I',
                ];
            } elseif (str_contains($tipo, 'queso')) {
                $template = 'datos-queso.xlsx';
                $filaDatos = 11;
                $mapaColumnas = [
                    'coliformes' => 'F',
                    'e. coli' => 'G',
                    'staphylococcu coagulasa' => 'H',
                    'mohos' => 'I',
                    'humedad' => 'J',
                    'solidos totales' => 'K',
                    'grasa' => 'L',
                ];
            } else {
                continue;
            }

            $templatePath = storage_path("app/plantillas/{$template}");
            if (!file_exists($templatePath)) continue;

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();

            // Fecha y hora
            $sheet->setCellValue("A{$filaDatos}", now()->format('Y-m-d'));
            $sheet->setCellValue("B{$filaDatos}", now()->format('H:i'));

            // Condiciones ambientales (fijo)
            $sheet->setCellValue("C{$filaDatos}", '22.4');
            $sheet->setCellValue("D{$filaDatos}", '43.4');

            // Código interno
            $sheet->setCellValue("E{$filaDatos}", $muestra->codigo_interno ?? '—');

            // RESULTADOS
            $ensayosRegistrados = [];

            foreach ($muestra->ensayos as $ens) {

                $nombre = strtolower($ens->nombre);
                $pivot = $ens->pivot;

                // CONCATENAR RESULTADO + OBSERVACIONES
                $resultado = trim(
                    ($pivot->resultado ?? '') .
                        ($pivot->observaciones ? "\nObs: " . $pivot->observaciones : "")
                );

                if ($resultado === '') $resultado = 'N/A';

                foreach ($mapaColumnas as $clave => $col) {
                    if (str_contains($nombre, $clave)) {

                        $sheet->setCellValue("{$col}{$filaDatos}", $resultado);

                        $sheet->getStyle("{$col}{$filaDatos}")
                            ->getAlignment()->setWrapText(true)
                            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                        $ensayosRegistrados[] = $clave;
                        break;
                    }
                }
            }

            // ENSAYOS NO REALIZADOS → N/A
            foreach ($mapaColumnas as $clave => $col) {
                if (!in_array($clave, $ensayosRegistrados)) {
                    $sheet->setCellValue("{$col}{$filaDatos}", "N/A");
                }
            }

            // Observaciones generales y VoBo
            $colObs = str_contains($tipo, 'leche') ? 'K' : 'M';
            $colVobo = str_contains($tipo, 'leche') ? 'L' : 'N';

            $sheet->setCellValue("{$colObs}{$filaDatos}", $muestra->condiciones ?? 'Sin observaciones');
            $sheet->setCellValue("{$colVobo}{$filaDatos}", 'Luis Rubiano');

            // Guardar archivo
            $fileName = 'Datos_' . $solicitud->numero_solicitud . '_' . ucfirst($muestra->tipoMuestra->nombre) . '.xlsx';
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

            $filePath = "{$tempDir}/{$fileName}";
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($filePath);

            $archivosGenerados[] = $filePath;
        }

        if (count($archivosGenerados) === 1) {
            return response()->download($archivosGenerados[0])->deleteFileAfterSend(true);
        }

        // ZIP
        $zipName = 'Datos_Solicitud_' . $solicitud->numero_solicitud . '.zip';
        $zipPath = storage_path("app/temp/{$zipName}");

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            foreach ($archivosGenerados as $archivo) {
                $zip->addFile($archivo, basename($archivo));
            }
            $zip->close();
        }

        foreach ($archivosGenerados as $archivo) @unlink($archivo);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function generarArchivosExcel($id)
    {
        $solicitud = Solicitud::with([
            'cliente.persona',
            'muestras.tipoMuestra',
            'muestras.ensayos'
        ])->findOrFail($id);

        if ($solicitud->muestras->isEmpty()) {
            throw new \Exception("No hay muestras registradas");
        }

        $archivosGenerados = [];

        foreach ($solicitud->muestras as $muestra) {

            $tipo = strtolower($muestra->tipoMuestra->nombre ?? '');

            // Plantilla según tipo de muestra
            if (str_contains($tipo, 'leche')) {
                $template = 'datos-leche.xlsx';
                $filaDatos = 11;
                $mapaColumnas = [
                    'aerobios' => 'F',
                    'grasa' => 'G',
                    'solidos totales' => 'H',
                    'densidad' => 'I',
                ];
            } elseif (str_contains($tipo, 'queso')) {
                $template = 'datos-queso.xlsx';
                $filaDatos = 11;
                $mapaColumnas = [
                    'coliformes' => 'F',
                    'e. coli' => 'G',
                    'staphylococcu coagulasa' => 'H',
                    'mohos' => 'I',
                    'humedad' => 'J',
                    'solidos totales' => 'K',
                    'grasa' => 'L',
                ];
            } else {
                continue;
            }

            $templatePath = storage_path("app/plantillas/{$template}");
            if (!file_exists($templatePath)) continue;

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();

            // Fecha, hora, condiciones ambientales
            $sheet->setCellValue("A{$filaDatos}", now()->format('Y-m-d'));
            $sheet->setCellValue("B{$filaDatos}", now()->format('H:i'));
            $sheet->setCellValue("C{$filaDatos}", '22.4');
            $sheet->setCellValue("D{$filaDatos}", '43.4');

            // Código interno
            $sheet->setCellValue("E{$filaDatos}", $muestra->codigo_interno ?? '—');

            // Resultados
            $ensayosRegistrados = [];

            foreach ($muestra->ensayos as $ens) {

                $nombre = strtolower($ens->nombre);
                $resultado = trim(
                    ($ens->pivot->resultado ?? '') .
                        ($ens->pivot->observaciones ? "\nObs: " . $ens->pivot->observaciones : '')
                );
                if ($resultado === '') $resultado = 'N/A';


                foreach ($mapaColumnas as $clave => $col) {
                    if (str_contains($nombre, $clave)) {

                        $sheet->setCellValue("{$col}{$filaDatos}", $resultado);

                        $sheet->getStyle("{$col}{$filaDatos}")
                            ->getAlignment()
                            ->setWrapText(true)
                            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                        $ensayosRegistrados[] = $clave;
                        break;
                    }
                }
            }

            // N/A para no realizados
            foreach ($mapaColumnas as $clave => $col) {
                if (!in_array($clave, $ensayosRegistrados)) {
                    $sheet->setCellValue("{$col}{$filaDatos}", "N/A");
                    $sheet->getStyle("{$col}{$filaDatos}")
                        ->getFont()->setItalic(true)->getColor()->setARGB('FF777777');
                    $sheet->getStyle("{$col}{$filaDatos}")
                        ->getAlignment()->setHorizontal('center')->setVertical('center');
                }
            }

            // Observaciones y VoBo
            $colObs = str_contains($tipo, 'leche') ? 'K' : 'M';
            $colVobo = str_contains($tipo, 'leche') ? 'L' : 'N';

            $sheet->setCellValue("{$colObs}{$filaDatos}", $muestra->condiciones ?? 'Sin observaciones');
            $sheet->setCellValue("{$colVobo}{$filaDatos}", 'Luis Rubiano');

            // Guardar archivo
            $fileName = 'Datos_' . $solicitud->numero_solicitud . '_' . ucfirst($muestra->tipoMuestra->nombre) . '.xlsx';
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

            $filePath = "{$tempDir}/{$fileName}";
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);

            $archivosGenerados[] = $filePath;
        }

        // Si un archivo → return ruta directa
        if (count($archivosGenerados) === 1) {
            return $archivosGenerados[0];
        }

        // Si varios → crear ZIP
        $zipName = 'Datos_Solicitud_' . $solicitud->numero_solicitud . '.zip';
        $zipPath = storage_path("app/temp/{$zipName}");

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($archivosGenerados as $archivo) {
            $zip->addFile($archivo, basename($archivo));
        }
        $zip->close();

        // Limpieza
        foreach ($archivosGenerados as $archivo) @unlink($archivo);

        return $zipPath;
    }

    public function enviarReporte(Request $request, $id)
    {
        $request->validate([
            'usuarios' => 'required|array|min:1',
        ]);

        $solicitud = Solicitud::with(['muestras.tipoMuestra'])->findOrFail($id);

        // Generar archivo temporal (ruta)
        $ruta = $this->generarArchivosExcel($id);

        // Enviar a usuarios seleccionados
        $usuarios = User::whereIn('id', $request->usuarios)->get();

        foreach ($usuarios as $usuario) {
            Mail::to($usuario->email)->send(new ReporteLaboratorioMail($solicitud, $ruta));
        }

        return back()->with('success', 'Reporte enviado correctamente a los gestores técnicos seleccionados.');
    }
}
