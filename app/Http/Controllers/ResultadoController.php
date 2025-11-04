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
        $solicitud = Solicitud::with(['muestras.muestraEnsayos'])->findOrFail($solicitudId);

        DB::transaction(function () use ($request, $solicitud) {
            $muestraEnsayoIds = $request->input('muestra_ensayo_id', []);
            $muestrasActualizadas = [];

            foreach ($muestraEnsayoIds as $muestraEnsayoId) {
                $muestraEnsayo = MuestraEnsayo::with('ensayo', 'muestra')->find($muestraEnsayoId);

                if (!$muestraEnsayo) {
                    continue;
                }

                $ensayo = $muestraEnsayo->ensayo;
                $ensayoNombre = $ensayo->nombre;
                $procesado = false;

                // 🧫 MICROBIOLOGÍA - Solo si tiene datos
                if (
                    $this->tieneDatosMicrobiologia($request, $muestraEnsayoId) &&
                    in_array($ensayoNombre, [
                        'Mohos y levaduras',
                        'Coliformes totales',
                        'E. coli',
                        'Staphylococcu coagulasa (+)',
                        'Aerobios mesófilos',
                    ])
                ) {
                    $this->procesarMicrobiologia($muestraEnsayo, $request);
                    $procesado = true;
                }
                // 🧈 GRASA - Solo si tiene datos
                elseif (
                    $this->tieneDatosGrasa($request, $muestraEnsayoId) &&
                    $ensayoNombre === 'Determinación de grasa'
                ) {
                    $this->procesarGrasa($muestraEnsayo, $request);
                    $procesado = true;
                }
                // 💧 SÓLIDOS TOTALES / HUMEDAD - Solo si tiene datos
                elseif (
                    $this->tieneDatosSolidos($request, $muestraEnsayoId) &&
                    in_array($ensayoNombre, ['Determinación de solidos totales', 'Determinación de humedad'])
                ) {
                    $this->procesarSolidos($muestraEnsayo, $request);
                    $procesado = true;
                }
                // ⚖️ DENSIDAD - Solo si tiene datos
                elseif (
                    $this->tieneDatosDensidad($request, $muestraEnsayoId) &&
                    $ensayoNombre === 'Determinación de densidad'
                ) {
                    $this->procesarDensidad($muestraEnsayo, $request);
                    $procesado = true;
                }

                // Actualizar fecha de análisis si se procesó algún dato
                if ($procesado) {
                    $muestraEnsayo->update([
                        'fecha_analisis' => now(),
                    ]);

                    // Guardar la muestra para actualizar después
                    if (!in_array($muestraEnsayo->muestra_id, $muestrasActualizadas)) {
                        $muestrasActualizadas[] = $muestraEnsayo->muestra_id;
                    }
                }
            }

            // Actualizar el estado de las muestras que fueron modificadas
            foreach ($muestrasActualizadas as $muestraId) {
                $muestra = Muestra::with('muestraEnsayos')->find($muestraId);
                if ($muestra) {
                    $estadoCalculado = $muestra->calcularEstado(); // Usar el método del modelo
                    $muestra->update(['estado' => $estadoCalculado]);
                }
            }

            // Actualizar el estado global de la solicitud
            $estadoGlobalCalculado = $solicitud->calcularEstadoGlobal(); // Usar el método del modelo
            $solicitud->update(['estado_global' => $estadoGlobalCalculado]);
        });

        return redirect()->route('resultados.index')
            ->with('success', 'Resultados guardados exitosamente.');
    }

    /**
     * Verificar si hay datos de microbiología para este ensayo
     */
    private function tieneDatosMicrobiologia(Request $request, $muestraEnsayoId): bool
    {
        return !empty($request->input("c1_{$muestraEnsayoId}")) ||
            !empty($request->input("c2_{$muestraEnsayoId}")) ||
            !empty($request->input("c3_{$muestraEnsayoId}")) ||
            !empty($request->input("c4_{$muestraEnsayoId}"));
    }

    /**
     * Verificar si hay datos de grasa para este ensayo
     */
    private function tieneDatosGrasa(Request $request, $muestraEnsayoId): bool
    {
        return !empty($request->input("a1_{$muestraEnsayoId}")) ||
            !empty($request->input("b1_{$muestraEnsayoId}")) ||
            !empty($request->input("a2_{$muestraEnsayoId}")) ||
            !empty($request->input("b2_{$muestraEnsayoId}"));
    }

    /**
     * Verificar si hay datos de sólidos para este ensayo
     */
    private function tieneDatosSolidos(Request $request, $muestraEnsayoId): bool
    {
        return !empty($request->input("m0_{$muestraEnsayoId}")) ||
            !empty($request->input("m1_{$muestraEnsayoId}")) ||
            !empty($request->input("m2_{$muestraEnsayoId}"));
    }

    /**
     * Verificar si hay datos de densidad para este ensayo
     */
    private function tieneDatosDensidad(Request $request, $muestraEnsayoId): bool
    {
        return !empty($request->input("densidad_{$muestraEnsayoId}"));
    }

    /**
     * 🧫 Procesar ensayos de microbiología
     */
    private function procesarMicrobiologia(MuestraEnsayo $muestraEnsayo, Request $request)
    {
        $id = $muestraEnsayo->id;

        $datosMicro = [
            'muestra_ensayo_id' => $id,
            'dilucion1_c1' => $request->input("c1_{$id}") ?: 0,
            'dilucion1_c2' => $request->input("c2_{$id}") ?: 0,
            'dilucion2_c1' => $request->input("c3_{$id}") ?: 0,
            'dilucion2_c2' => $request->input("c4_{$id}") ?: 0,
            'unidad' => $muestraEnsayo->ensayo->unidad_medida, // Ahora acepta cualquier VARCHAR
        ];

        // Calcular resultado según la fórmula: (suma de todas las diluciones / 4) * 1000
        $c1 = floatval($datosMicro['dilucion1_c1']);
        $c2 = floatval($datosMicro['dilucion1_c2']);
        $c3 = floatval($datosMicro['dilucion2_c1']);
        $c4 = floatval($datosMicro['dilucion2_c2']);

        $resultado = (($c1 + $c2 + $c3 + $c4) / 4) * 1000;
        $datosMicro['resultado'] = round($resultado, 2);

        // Guardar o actualizar en microbiología
        EnsayoMicrobiologia::updateOrCreate(
            ['muestra_ensayo_id' => $id],
            $datosMicro
        );

        // Actualizar resultado en la tabla pivot
        $muestraEnsayo->update([
            'resultado' => $datosMicro['resultado'],
            'unidad_medida' => $datosMicro['unidad'],
        ]);
    }

    /**
     * 🧈 Procesar ensayo de grasa
     */
    private function procesarGrasa(MuestraEnsayo $muestraEnsayo, Request $request)
    {
        $id = $muestraEnsayo->id;

        $datosGrasa = [
            'muestra_ensayo_id' => $id,
            'tipo' => 'grasa',
            'replica1_a' => $request->input("a1_{$id}") ?: 0,
            'replica1_b' => $request->input("b1_{$id}") ?: 0,
            'replica2_a' => $request->input("a2_{$id}") ?: 0,
            'replica2_b' => $request->input("b2_{$id}") ?: 0,
            'unidad_grasa' => $muestraEnsayo->ensayo->unidad_medida, // Ahora acepta cualquier VARCHAR
        ];

        // Calcular resultado de grasa: promedio de ((B - A) réplica 1 y réplica 2)
        $b1 = floatval($datosGrasa['replica1_b']);
        $a1 = floatval($datosGrasa['replica1_a']);
        $b2 = floatval($datosGrasa['replica2_b']);
        $a2 = floatval($datosGrasa['replica2_a']);

        $r1 = $b1 - $a1;
        $r2 = $b2 - $a2;
        $promedio = ($r1 + $r2) / 2;
        $datosGrasa['resultado_grasa'] = round($promedio, 2);

        // Guardar o actualizar en fisicoquímico
        EnsayoFisicoquimico::updateOrCreate(
            ['muestra_ensayo_id' => $id],
            $datosGrasa
        );

        // Actualizar resultado en la tabla pivot
        $muestraEnsayo->update([
            'resultado' => $datosGrasa['resultado_grasa'],
            'unidad_medida' => $datosGrasa['unidad_grasa'],
        ]);
    }

    /**
     * 💧 Procesar sólidos totales y humedad
     */
    private function procesarSolidos(MuestraEnsayo $muestraEnsayo, Request $request)
    {
        $id = $muestraEnsayo->id;
        $ensayoNombre = $muestraEnsayo->ensayo->nombre;

        $datosSolidos = [
            'muestra_ensayo_id' => $id,
            'tipo' => $ensayoNombre === 'Determinación de solidos totales' ? 'solidos_totales' : 'humedad',
            'replica1_m0' => $request->input("m0_{$id}") ?: 0,
            'replica1_m1' => $request->input("m1_{$id}") ?: 0,
            'replica1_m2' => $request->input("m2_{$id}") ?: 0,
        ];

        // Calcular resultado: ((m2 - m0) / (m1 - m0)) * 100
        $m0 = floatval($datosSolidos['replica1_m0']);
        $m1 = floatval($datosSolidos['replica1_m1']);
        $m2 = floatval($datosSolidos['replica1_m2']);

        $numerador = $m2 - $m0;
        $denominador = $m1 - $m0;

        $resultado = $denominador != 0 ? ($numerador / $denominador) * 100 : 0;
        $datosSolidos['resultado_porcentaje'] = round($resultado, 2);

        // Guardar o actualizar en fisicoquímico
        EnsayoFisicoquimico::updateOrCreate(
            ['muestra_ensayo_id' => $id],
            $datosSolidos
        );

        // Actualizar resultado en la tabla pivot
        $muestraEnsayo->update([
            'resultado' => $datosSolidos['resultado_porcentaje'],
            'unidad_medida' => '%',
        ]);
    }

    /**
     * ⚖️ Procesar densidad (solo observaciones)
     */
    private function procesarDensidad(MuestraEnsayo $muestraEnsayo, Request $request)
    {
        $id = $muestraEnsayo->id;

        $observaciones = $request->input("densidad_{$id}");

        if (!empty($observaciones)) {
            $datosDensidad = [
                'muestra_ensayo_id' => $id,
                'tipo' => 'densidad',
                'densidad' => $observaciones,
            ];

            // Guardar o actualizar en fisicoquímico
            EnsayoFisicoquimico::updateOrCreate(
                ['muestra_ensayo_id' => $id],
                $datosDensidad
            );

            // Para densidad, guardamos las observaciones en la tabla pivot
            $muestraEnsayo->update([

                'resultado' => $observaciones,
            ]);
        }
    }



    // public function update(Request $request, $id)
    // {
    //     $muestra = Muestra::findOrFail($id);

    //     foreach ($request->ensayos as $muestraId => $ensayos) {
    //         $m = Muestra::find($muestraId);
    //         $completos = true;

    //         foreach ($ensayos as $ensayoId => $datos) {
    //             $m->ensayos()->updateExistingPivot($ensayoId, [
    //                 'fecha_analisis' => $datos['fecha_analisis'],
    //                 'resultado' => $datos['resultado'],
    //                 'unidad_medida' => $datos['unidad_medida'],
    //                 'codigo_trazabilidad' => $m->codigo_interno,
    //             ]);

    //             if (empty($datos['resultado'])) {
    //                 $completos = false;
    //             }
    //         }

    //         $m->update(['estado' => $completos ? 'finalizada' : 'en_proceso']);
    //     }

    //     // ✅ Obtener el nombre del rol desde la relación
    //     $usuario = auth()->user();
    //     $rolNombre = $usuario->role->nombre;

    //     $mensaje = '✅ Resultados registrados correctamente.';

    //     if ($rolNombre === 'analista') {
    //         return redirect()->route('resultados.index')->with('success', $mensaje);
    //     }

    //     if ($rolNombre === 'gestor_tecnico') {
    //         return redirect()->route('dashboard.gestor')->with('success', $mensaje);
    //     }

    //     // Por defecto (admin u otros)
    //     return redirect()->route('dashboard.admin')->with('success', $mensaje);
    // }




    // public function exportarDatos($id)
    // {
    //     $solicitud = Solicitud::with([
    //         'cliente.persona',
    //         'muestras.tipoMuestra',
    //         'muestras.ensayos'
    //     ])->findOrFail($id);

    //     if ($solicitud->muestras->isEmpty()) {
    //         return back()->with('error', 'No hay muestras registradas para esta solicitud.');
    //     }

    //     $archivosGenerados = [];

    //     foreach ($solicitud->muestras as $muestra) {
    //         $tipo = strtolower($muestra->tipoMuestra->nombre ?? '');
    //         $tipoMuestraId = $muestra->tipo_muestra_id;

    //         // Seleccionar plantilla
    //         if (str_contains($tipo, 'leche')) {
    //             $template = 'datos-leche.xlsx';
    //             $filaDatos = 11;
    //             $mapaColumnas = [
    //                 'aerobios' => 'F',
    //                 'grasa' => 'G',
    //                 'solidos totales' => 'H',
    //                 'densidad' => 'I',
    //             ];
    //             $colObs = 'K';
    //             $colVobo = 'L';
    //         } elseif (str_contains($tipo, 'queso')) {
    //             $template = 'datos-queso.xlsx';
    //             $filaDatos = 11;
    //             $mapaColumnas = [
    //                 'coliformes' => 'F',
    //                 'e. coli' => 'G',
    //                 'staphylococcus' => 'H',
    //                 'mohos' => 'I',
    //                 'humedad' => 'J',
    //                 'solidos totales' => 'K',
    //                 'grasa' => 'L',
    //             ];
    //             $colObs = 'M';
    //             $colVobo = 'N';
    //         } else {
    //             continue;
    //         }

    //         $templatePath = storage_path("app/plantillas/{$template}");
    //         if (!file_exists($templatePath)) continue;

    //         $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
    //         $sheet = $spreadsheet->getActiveSheet();

    //         // --- Fecha y hora ---
    //         $sheet->setCellValue("A{$filaDatos}", now()->format('Y-m-d'));
    //         $sheet->setCellValue("B{$filaDatos}", now()->format('H:i'));

    //         // --- Condiciones ambientales ---
    //         $sheet->setCellValue("C{$filaDatos}", '22.4'); // Temperatura
    //         $sheet->setCellValue("D{$filaDatos}", '43.4'); // Humedad relativa

    //         // --- Código interno ---
    //         $sheet->setCellValue("E{$filaDatos}", $muestra->codigo_interno ?? '—');

    //         // --- Recorrer ensayos ---
    //         foreach ($muestra->ensayos as $ens) {
    //             $nombre = strtolower($ens->nombre);
    //             $pivot = $ens->pivot;
    //             $muestraEnsayoId = $pivot->id;
    //             $textoCelda = '';

    //             // Buscar registros asociados
    //             $micro = \App\Models\EnsayoMicrobiologia::where('muestra_ensayo_id', $muestraEnsayoId)->first();
    //             $fisico = \App\Models\EnsayoFisicoquimico::where('muestra_ensayo_id', $muestraEnsayoId)->first();

    //             // 🦠 MICROBIOLOGÍA
    //             if ($micro) {
    //                 $unidad = str_contains($tipo, 'leche') ? 'UFC/mL' : 'UFC/g';
    //                 $textoCelda =
    //                     "Dilución 1:\n" .
    //                     "  C1: {$micro->dilucion1_c1}\n" .
    //                     "  C2: {$micro->dilucion1_c2}\n" .
    //                     "Dilución 2:\n" .
    //                     "  C1: {$micro->dilucion2_c1}\n" .
    //                     "  C2: {$micro->dilucion2_c2}\n" .
    //                     "Resultado: {$micro->resultado} {$unidad}\n" .
    //                     "Fórmula: (Σ de todas las diluciones / 4) * 1000";
    //             }

    //             // 🧪 FÍSICOQUÍMICO – GRASA
    //             elseif ($fisico && $fisico->tipo === 'grasa') {
    //                 $unidad = str_contains($tipo, 'leche') ? 'ml/100ml' : 'g/100g';
    //                 $textoCelda =
    //                     "Réplica 1:\n" .
    //                     "  Menisco superior (B): {$fisico->replica1_b}\n" .
    //                     "  Menisco inferior (A): {$fisico->replica1_a}\n" .
    //                     "Réplica 2:\n" .
    //                     "  Menisco superior (B): {$fisico->replica2_b}\n" .
    //                     "  Menisco inferior (A): {$fisico->replica2_a}\n" .
    //                     "Resultado: {$fisico->resultado_grasa} {$unidad}\n" .
    //                     "Fórmula: Promedio de (B - A)";
    //             }

    //             // 🌡️ FÍSICOQUÍMICO – HUMEDAD o SÓLIDOS
    //             elseif ($fisico && in_array($fisico->tipo, ['solidos_totales', 'humedad'])) {
    //                 $textoCelda =
    //                     "Réplica 1:\n" .
    //                     "  m0: {$fisico->replica1_m0}\n" .
    //                     "  m1: {$fisico->replica1_m1}\n" .
    //                     "  m2: {$fisico->replica1_m2}\n" .
    //                     "Resultado: {$fisico->resultado_porcentaje} %\n" .
    //                     "Fórmula: ((m2 - m0) / (m1 - m0)) * 100";
    //             }

    //             // 📏 DENSIDAD
    //             elseif ($fisico && $fisico->tipo === 'densidad') {
    //                 $textoCelda = "Densidad: {$fisico->densidad}";
    //             }

    //             // Si no se encuentra ningún detalle
    //             if (empty($textoCelda)) {
    //                 // Si no hay registro de microbiología o fisicoquímica
    //                 // y tampoco hay resultado en el pivot → no aplica
    //                 if (is_null($pivot->resultado) || $pivot->resultado === '') {
    //                     $textoCelda = 'N/A';
    //                 } else {
    //                     $textoCelda = $pivot->resultado;
    //                 }
    //             }


    //             // Buscar columna correspondiente
    //             foreach ($mapaColumnas as $clave => $col) {
    //                 if (str_contains($nombre, $clave)) {
    //                     $sheet->setCellValue("{$col}{$filaDatos}", $textoCelda);
    //                     $sheet->getStyle("{$col}{$filaDatos}")
    //                         ->getAlignment()
    //                         ->setWrapText(true)
    //                         ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
    //                     break;
    //                 }
    //             }
    //         }

    //         // Observaciones y VoBo
    //         $sheet->setCellValue("{$colObs}{$filaDatos}", $muestra->condiciones ?? 'Sin observaciones');
    //         $sheet->setCellValue("{$colVobo}{$filaDatos}", 'Luis Rubiano');

    //         // Guardar archivo
    //         $fileName = 'Datos_' . $solicitud->numero_solicitud . '_' . ucfirst($muestra->tipoMuestra->nombre) . '.xlsx';
    //         $tempDir = storage_path('app/temp');
    //         if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
    //         $filePath = "{$tempDir}/{$fileName}";

    //         $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    //         $writer->save($filePath);
    //         $archivosGenerados[] = $filePath;
    //     }

    //     // --- Descargar ---
    //     if (count($archivosGenerados) === 1) {
    //         return response()->download($archivosGenerados[0])->deleteFileAfterSend(true);
    //     }

    //     $zipName = 'Datos_Solicitud_' . $solicitud->numero_solicitud . '.zip';
    //     $zipPath = storage_path("app/temp/{$zipName}");
    //     $zip = new \ZipArchive();

    //     if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
    //         foreach ($archivosGenerados as $archivo) {
    //             $zip->addFile($archivo, basename($archivo));
    //         }
    //         $zip->close();
    //     }

    //     foreach ($archivosGenerados as $archivo) @unlink($archivo);

    //     return response()->download($zipPath)->deleteFileAfterSend(true);
    // }

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
            $tipoMuestraId = $muestra->tipo_muestra_id;

            // Selección de plantilla
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
                    'staphylococcu coagulasa ' => 'H',
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

            // --- Fecha y hora
            $sheet->setCellValue("A{$filaDatos}", now()->format('Y-m-d'));
            $sheet->setCellValue("B{$filaDatos}", now()->format('H:i'));

            // --- Condiciones ambientales
            $sheet->setCellValue("C{$filaDatos}", '22.4');
            $sheet->setCellValue("D{$filaDatos}", '43.4');

            // --- Código interno
            $sheet->setCellValue("E{$filaDatos}", $muestra->codigo_interno ?? '—');

            // --- Ensayos con datos
            $ensayosRegistrados = [];

            foreach ($muestra->ensayos as $ens) {
                $nombre = strtolower($ens->nombre);
                $pivot = $ens->pivot;
                $muestraEnsayoId = $pivot->id;
                $textoCelda = null;

                $micro = \App\Models\EnsayoMicrobiologia::where('muestra_ensayo_id', $muestraEnsayoId)->first();
                $fisico = \App\Models\EnsayoFisicoquimico::where('muestra_ensayo_id', $muestraEnsayoId)->first();

                if ($micro) {
                    $unidad = str_contains($tipo, 'leche') ? 'UFC/mL' : 'UFC/g';
                    $textoCelda =
                        "Dilución 1:\n" .
                        "  C1: {$micro->dilucion1_c1}\n" .
                        "  C2: {$micro->dilucion1_c2}\n" .
                        "Dilución 2:\n" .
                        "  C1: {$micro->dilucion2_c1}\n" .
                        "  C2: {$micro->dilucion2_c2}\n" .
                        "Resultado: {$micro->resultado} {$unidad}\n" .
                        "Fórmula: (Σ diluciones / 4) * 1000";
                } elseif ($fisico && $fisico->tipo === 'grasa') {
                    $unidad = str_contains($tipo, 'leche') ? 'ml/100ml' : 'g/100g';
                    $textoCelda =
                        "Réplica 1:\n" .
                        "  Menisco sup. (B): {$fisico->replica1_b}\n" .
                        "  Menisco inf. (A): {$fisico->replica1_a}\n" .
                        "Réplica 2:\n" .
                        "  Menisco sup. (B): {$fisico->replica2_b}\n" .
                        "  Menisco inf. (A): {$fisico->replica2_a}\n" .
                        "Resultado: {$fisico->resultado_grasa} {$unidad}\n" .
                        "Fórmula: Promedio de (B - A)";
                } elseif ($fisico && in_array($fisico->tipo, ['solidos_totales', 'humedad'])) {
                    $textoCelda =
                        "Réplica 1:\n" .
                        "  m0: {$fisico->replica1_m0}\n" .
                        "  m1: {$fisico->replica1_m1}\n" .
                        "  m2: {$fisico->replica1_m2}\n" .
                        "Resultado: {$fisico->resultado_porcentaje} %\n" .
                        "Fórmula: ((m2 - m0) / (m1 - m0)) * 100";
                } elseif ($fisico && $fisico->tipo === 'densidad') {
                    $textoCelda = "Densidad: {$fisico->densidad}";
                }

                // Si no se encontró información, será N/A
                if (empty($textoCelda)) {
                    $textoCelda = 'N/A';
                }

                // Colocar en su columna
                foreach ($mapaColumnas as $clave => $col) {
                    if (str_contains($nombre, $clave)) {
                        $sheet->setCellValue("{$col}{$filaDatos}", $textoCelda);
                        $sheet->getStyle("{$col}{$filaDatos}")
                            ->getAlignment()
                            ->setWrapText(true)
                            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                        $ensayosRegistrados[] = $clave;
                        break;
                    }
                }
            }

            // --- Ahora marcamos como N/A los ensayos que NO existen en esta muestra
            foreach ($mapaColumnas as $clave => $col) {
                if (!in_array($clave, $ensayosRegistrados)) {
                    $sheet->setCellValue("{$col}{$filaDatos}", "N/A");
                    $sheet->getStyle("{$col}{$filaDatos}")
                        ->getFont()->setItalic(true)->getColor()->setARGB('FF777777');
                    $sheet->getStyle("{$col}{$filaDatos}")
                        ->getAlignment()->setHorizontal('center')->setVertical('center');
                }
            }

            // --- Observaciones y VoBo
            $colObs = str_contains($tipo, 'leche') ? 'K' : 'M';
            $colVobo = str_contains($tipo, 'leche') ? 'L' : 'N';
            $sheet->setCellValue("{$colObs}{$filaDatos}", $muestra->condiciones ?? 'Sin observaciones');
            $sheet->setCellValue("{$colVobo}{$filaDatos}", 'Luis Rubiano');

            // --- Guardar archivo
            $fileName = 'Datos_' . $solicitud->numero_solicitud . '_' . ucfirst($muestra->tipoMuestra->nombre) . '.xlsx';
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
            $filePath = "{$tempDir}/{$fileName}";

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);

            $archivosGenerados[] = $filePath;
        }

        // --- Descarga o ZIP
        if (count($archivosGenerados) === 1) {
            return response()->download($archivosGenerados[0])->deleteFileAfterSend(true);
        }

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

    public function enviarReporte(Request $request, $id)
    {
        $request->validate([
            'usuarios' => 'required|array|min:1',
        ]);

        $solicitud = Solicitud::with(['muestras.tipoMuestra'])->findOrFail($id);

        // Genera el archivo Excel temporalmente (usa tu método actual)
        $archivo = app()->call([$this, 'exportarDatos'], ['id' => $id]);
        $ruta = $archivo->getFile()->getPathname();

        // Obtiene los usuarios seleccionados
        $usuarios = User::whereIn('id', $request->usuarios)->get();

        foreach ($usuarios as $usuario) {
            Mail::to($usuario->email)->send(new ReporteLaboratorioMail($solicitud, $ruta));
        }

        return back()->with('success', 'Reporte enviado correctamente a los gestores técnicos seleccionados.');
    }
}
