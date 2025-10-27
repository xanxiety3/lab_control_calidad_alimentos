<?php

namespace App\Http\Controllers;

use App\Mail\ResultadoMailable;
use App\Models\Muestra;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ResultadoController extends Controller
{
    public function index()
    {
        $solicitudes = Solicitud::with(['cliente.persona', 'muestras'])->orderByDesc('created_at')->get();

        foreach ($solicitudes as $solicitud) {
            $total = $solicitud->muestras->count();
            $pendientes = $solicitud->muestras->where('estado', 'pendiente')->count();
            $finalizadas = $solicitud->muestras->where('estado', 'finalizada')->count();

            if ($pendientes === $total) {
                $solicitud->estado_global = 'pendiente';
            } elseif ($finalizadas === $total) {
                $solicitud->estado_global = 'finalizada';
            } else {
                $solicitud->estado_global = 'en_proceso';
            }
        }

        return view('dashboard.analista', compact('solicitudes'));
    }


    public function edit($id)
    {
        // Buscar la muestra seleccionada
        $muestra = Muestra::with('solicitud')->findOrFail($id);

        // Obtener todas las muestras que pertenecen a la misma solicitud
        $muestras = Muestra::with(['ensayos'])
            ->where('solicitud_id', $muestra->solicitud_id)
            ->get();

        return view('resultados.edit', compact('muestra', 'muestras'));
    }




    public function update(Request $request, $id)
    {
        $muestra = Muestra::findOrFail($id);

        foreach ($request->ensayos as $muestraId => $ensayos) {
            $m = Muestra::find($muestraId);
            $completos = true;

            foreach ($ensayos as $ensayoId => $datos) {
                $m->ensayos()->updateExistingPivot($ensayoId, [
                    'fecha_analisis' => $datos['fecha_analisis'],
                    'resultado' => $datos['resultado'],
                    'unidad_medida' => $datos['unidad_medida'],
                    'codigo_trazabilidad' => $m->codigo_interno,
                ]);

                if (empty($datos['resultado'])) {
                    $completos = false;
                }
            }

            $m->update(['estado' => $completos ? 'finalizada' : 'en_proceso']);
        }

        return redirect()->route('resultados.index')
            ->with('success', 'Resultados registrados correctamente.');
    }


    public function exportar($id)
    {
        $solicitud = Solicitud::with([
            'cliente.persona',
            'cliente.municipio.departamento',
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

            // 🧩 Seleccionar plantilla
            if (str_contains($tipo, 'leche')) {
                $template = 'formato_leche.xlsx';
                $filaResultados = 22;
            } elseif (str_contains($tipo, 'queso')) {
                $template = 'formato_queso.xlsx';
                $filaResultados = 22;
            } else {
                continue;
            }

            $templatePath = storage_path("app/plantillas/{$template}");
            if (!file_exists($templatePath)) continue;

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();

            // 🧾 Datos del cliente
            $cliente = $solicitud->cliente;
            $persona = $cliente->persona;
            $sheet->setCellValue('B7', $persona->nombre_completo ?? '');
            $sheet->setCellValue('B8', $persona->numero_documento ?? '');
            $sheet->setCellValue('B9', $cliente->direccion ?? '');
            $sheet->setCellValue('B10', $cliente->correo_electronico ?? '');
            $sheet->setCellValue('B11', $cliente->municipio->departamento->nombre ?? '');
            $sheet->setCellValue('B12', $cliente->municipio->nombre ?? '');
            $sheet->setCellValue('B13', $cliente->telefono ?? '');
            // 🧾 Datos del análisis y muestra
            $tipoMuestraNombre = ucfirst(strtolower($muestra->tipoMuestra->nombre ?? 'Desconocida'));

            // Análisis solicitado (B14)
            $sheet->setCellValue('B14', $tipoMuestraNombre);
            $sheet->mergeCells('B14:M14');

            // Información de la muestra
            $sheet->setCellValue('B16', $tipoMuestraNombre); // Matriz
            $sheet->mergeCells('B16:E16');



            // Fecha de muestreo (actual)
            $sheet->setCellValue('B17', now()->format('Y-m-d'));

            // Fecha de reporte (fecha de recepción)
            $fechaRecepcion = $muestra->created_at ? \Carbon\Carbon::parse($muestra->created_at)->format('Y-m-d') : now()->format('Y-m-d');
            $sheet->setCellValue('B18', $fechaRecepcion);


            // Estilos
            $gris = [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFDADADA']
                ]
            ];

            $centrado = [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ];

            // Traer ensayos del tipo de muestra
            $ensayosBD = \App\Models\Ensayo::where('tipo_muestra_id', $tipoMuestraId)
                ->where('activo', true)
                ->orderBy('id')
                ->get();

            $resultados = [];
            foreach ($muestra->ensayos as $ens) {
                $resultados[$ens->id] = [
                    'resultado' => $ens->pivot->resultado,
                    'fecha_analisis' => $ens->pivot->fecha_analisis,
                    'unidad' => $ens->pivot->unidad_medida,
                    'codigo_trazabilidad' => $ens->pivot->codigo_trazabilidad
                ];
            }

            // 🧪 Escribir resultados según tipo de muestra
            if (str_contains($tipo, 'leche')) {
                // Mapeo por NOMBRE → columnas fijas en la plantilla
                $mapaColumnas = [
                    'grasa' => 'D',
                    'solidos totales' => 'E', // E y F se fusionan
                    'aerobios mesófilos' => 'G', // G y H se fusionan
                    'densidad' => 'I', // I y J se fusionan
                ];

                foreach ($ensayosBD as $ensayo) {
                    $nombre = strtolower($ensayo->nombre);
                    $valor = $resultados[$ensayo->id]['resultado'] ?? null;

                    // Buscar si el nombre del ensayo existe en el mapa
                    foreach ($mapaColumnas as $clave => $col) {
                        if (str_contains($nombre, $clave)) {
                            // Fusionar celdas donde corresponde
                            if ($col === 'E') $sheet->mergeCells("E{$filaResultados}:F{$filaResultados}");
                            if ($col === 'G') $sheet->mergeCells("G{$filaResultados}:H{$filaResultados}");
                            if ($col === 'I') $sheet->mergeCells("I{$filaResultados}:J{$filaResultados}");

                            // Escribir valor o sombrear
                            if (empty($valor)) {
                                $sheet->setCellValue("{$col}{$filaResultados}", '-----');
                                $sheet->getStyle("{$col}{$filaResultados}")->applyFromArray($gris);
                            } else {
                                $sheet->setCellValue("{$col}{$filaResultados}", $valor);
                            }

                            $sheet->getStyle("{$col}{$filaResultados}")->applyFromArray($centrado);
                            break;
                        }
                    }
                }

                // Concatenar texto con número de solicitud
                $textoSolicitud = 'Número de solicitud: ' . ($solicitud->numero_solicitud ?? '---');
                $sheet->setCellValue('F16', $textoSolicitud);
                $sheet->mergeCells('F16:L18');

                // Código trazabilidad (merge K:L)
                $sheet->mergeCells("K{$filaResultados}:L{$filaResultados}");
                $sheet->setCellValue("K{$filaResultados}", $muestra->ensayos->first()->pivot->codigo_trazabilidad ?? '-----');
                $sheet->getStyle("K{$filaResultados}:L{$filaResultados}")->applyFromArray($centrado);
            } elseif (str_contains($tipo, 'queso')) {
                // QUESO → columnas D, E, F, G, H, I, J
                $mapaColumnas = [
                    'grasa' => 'D',
                    'solidos totales' => 'E',
                    'humedad' => 'F',
                    'mohos y levaduras' => 'G',
                    'coliformes totales' => 'H',
                    'e. coli' => 'I',
                    'staphylococcu coagulasa (+)' => 'J',
                ];

                foreach ($ensayosBD as $ensayo) {
                    $nombre = strtolower($ensayo->nombre);
                    $valor = $resultados[$ensayo->id]['resultado'] ?? null;

                    foreach ($mapaColumnas as $clave => $col) {
                        if (str_contains($nombre, $clave)) {
                            if (empty($valor)) {
                                $sheet->setCellValue("{$col}{$filaResultados}", '-----');
                                $sheet->getStyle("{$col}{$filaResultados}")->applyFromArray($gris);
                            } else {
                                $sheet->setCellValue("{$col}{$filaResultados}", $valor);
                            }
                            $sheet->getStyle("{$col}{$filaResultados}")->applyFromArray($centrado);
                            break;
                        }
                    }
                }
                // Concatenar texto con número de solicitud
                $textoSolicitud = 'Número de solicitud: ' . ($solicitud->numero_solicitud ?? '---');
                $sheet->setCellValue('F16', $textoSolicitud);
                $sheet->mergeCells('F16:M18');
                // Código trazabilidad → K
                $sheet->setCellValue("K{$filaResultados}", $muestra->ensayos->first()->pivot->codigo_trazabilidad ?? '-----');
                $sheet->getStyle("K{$filaResultados}")->applyFromArray($centrado);
            }


            // --- Datos fijos de muestra
            $sheet->setCellValue("A{$filaResultados}", $muestra->codigo_cliente ?? '');
            $sheet->setCellValue("B{$filaResultados}", $muestra->created_at ?? '');
            $sheet->setCellValue("C{$filaResultados}", $muestra->ensayos->first()->pivot->fecha_analisis ?? '');

            // --- Tabla inferior (ensayo / intervalo / referencia)
            $filaTabla = 36;
            foreach ($ensayosBD as $ensayo) {
                $sheet->mergeCells("A{$filaTabla}:C{$filaTabla}");
                $sheet->mergeCells("E{$filaTabla}:G{$filaTabla}");
                $sheet->mergeCells("I{$filaTabla}:K{$filaTabla}");

                $sheet->setCellValue("A{$filaTabla}", $ensayo->nombre);
                $sheet->setCellValue("E{$filaTabla}", $ensayo->intervalo_medicion ?? '');
                $sheet->setCellValue("I{$filaTabla}", $ensayo->metodo_norma ?? '');

                $filaTabla++;
            }

            // --- Guardar archivo
            $fileName = 'Resultados_' . $solicitud->numero_solicitud . '_' . ucfirst($muestra->tipoMuestra->nombre) . '.xlsx';
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
            $filePath = "{$tempDir}/{$fileName}";

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);

            $archivosGenerados[] = $filePath;
        }

        // --- Descarga o zip
        if (count($archivosGenerados) === 1) {
            return response()->download($archivosGenerados[0])->deleteFileAfterSend(true);
        }

        $zipName = 'Resultados_Solicitud_' . $solicitud->numero_solicitud . '.zip';
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

 
  
}
