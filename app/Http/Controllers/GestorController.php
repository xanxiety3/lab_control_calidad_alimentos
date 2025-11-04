<?php

namespace App\Http\Controllers;

use App\Models\EnsayoFisicoquimico;
use App\Models\EnsayoMicrobiologia;
use App\Models\Muestra;
use App\Models\MuestraEnsayo;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class GestorController extends Controller
{

    public function index()
    {
        $solicitudes = Solicitud::with('cliente.persona', 'muestras.tipoMuestra')
            ->whereHas('muestras', function ($q) {
                $q->whereRaw("(
            SELECT COUNT(*) FROM muestra_ensayo 
            WHERE muestra_ensayo.muestra_id = muestras.id 
            AND (resultado IS NULL OR resultado = '' OR resultado = 0)
        ) = 0");
            })
            ->get();


        return view('dashboard.gestor', compact('solicitudes'));
    }


    public function revisar($id)
    {
        $solicitud = \App\Models\Solicitud::with([
            'cliente.persona',
            'muestras.tipoMuestra',
            'muestras.muestraEnsayos.ensayo',
            'muestras.muestraEnsayos.fisicoquimico',
            'muestras.muestraEnsayos.microbiologia'
        ])->findOrFail($id);

        return view('gestor.revisar', compact('solicitud'));
    }

    public function ver($id)
    {
        $muestra = Muestra::with([
            'tipoMuestra',
            'muestraEnsayos.ensayo',
            'muestraEnsayos.fisicoquimico',
            'muestraEnsayos.microbiologia'
        ])->findOrFail($id);

        return view('gestor.show', compact('muestra'));
    }


    // ✅ Aprobación / ❌ Rechazo
    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:aprobada,rechazada',
            'observacion' => 'nullable|string|max:500'
        ]);

        $muestra = Muestra::findOrFail($id);
        $muestra->estado = $request->estado;
        $muestra->condiciones = $request->observacion;
        $muestra->save();

        return redirect()->route('dashboard.gestor')
            ->with('success', '✅ Revisión registrada correctamente.');
    }


  public function actualizarMuestra(Request $request, $id)
{
    // 🔹 Actualizar microbiología
    if ($request->has('micro')) {
        foreach ($request->micro as $data) {
            \App\Models\EnsayoMicrobiologia::where('id', $data['id'])->update([
                'dilucion1_c1' => $data['dilucion1_c1'] ?? null,
                'dilucion1_c2' => $data['dilucion1_c2'] ?? null,
                'dilucion2_c1' => $data['dilucion2_c1'] ?? null,
                'dilucion2_c2' => $data['dilucion2_c2'] ?? null,
                'resultado' => $data['resultado'] ?? null,
            ]);
        }
    }

    // 🔹 Actualizar fisicoquímicos
    if ($request->has('fisico')) {
        foreach ($request->fisico as $data) {
            \App\Models\EnsayoFisicoquimico::where('id', $data['id'])->update([
                'replica1_a' => $data['replica1_a'] ?? null,
                'replica1_b' => $data['replica1_b'] ?? null,
                'replica2_a' => $data['replica2_a'] ?? null,
                'replica2_b' => $data['replica2_b'] ?? null,
                'replica1_m0' => $data['replica1_m0'] ?? null,
                'replica1_m1' => $data['replica1_m1'] ?? null,
                'replica1_m2' => $data['replica1_m2'] ?? null,
                'resultado_grasa' => $data['resultado_grasa'] ?? null,
                'resultado_porcentaje' => $data['resultado_porcentaje'] ?? null,
                'densidad' => $data['densidad'] ?? null,
            ]);
        }
    }

    return back()->with('success', '✅ Resultados de la muestra actualizados correctamente.');
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
                $template = 'formato_leche-final.xlsx';
                $filaResultados = 22;
            } elseif (str_contains($tipo, 'queso')) {
                $template = 'formato_queso-final.xlsx';
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

    public function enviarCorreo($id)
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

            $tipoMuestraNombre = ucfirst(strtolower($muestra->tipoMuestra->nombre ?? 'Desconocida'));
            $sheet->setCellValue('B14', $tipoMuestraNombre);
            $sheet->mergeCells('B14:M14');
            $sheet->setCellValue('B16', $tipoMuestraNombre);
            $sheet->mergeCells('B16:E16');

            $sheet->setCellValue('B17', now()->format('Y-m-d'));
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

            // Traer ensayos del tipo
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

            // 🧪 Resultados por tipo
            if (str_contains($tipo, 'leche')) {
                $mapaColumnas = [
                    'grasa' => 'D',
                    'solidos totales' => 'E',
                    'aerobios mesófilos' => 'G',
                    'densidad' => 'I',
                ];

                foreach ($ensayosBD as $ensayo) {
                    $nombre = strtolower($ensayo->nombre);
                    $valor = $resultados[$ensayo->id]['resultado'] ?? null;

                    foreach ($mapaColumnas as $clave => $col) {
                        if (str_contains($nombre, $clave)) {
                            if ($col === 'E') $sheet->mergeCells("E{$filaResultados}:F{$filaResultados}");
                            if ($col === 'G') $sheet->mergeCells("G{$filaResultados}:H{$filaResultados}");
                            if ($col === 'I') $sheet->mergeCells("I{$filaResultados}:J{$filaResultados}");

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

                $textoSolicitud = 'Número de solicitud: ' . ($solicitud->numero_solicitud ?? '---');
                $sheet->setCellValue('F16', $textoSolicitud);
                $sheet->mergeCells('F16:L18');

                $sheet->mergeCells("K{$filaResultados}:L{$filaResultados}");
                $sheet->setCellValue("K{$filaResultados}", $muestra->ensayos->first()->pivot->codigo_trazabilidad ?? '-----');
                $sheet->getStyle("K{$filaResultados}:L{$filaResultados}")->applyFromArray($centrado);
            } elseif (str_contains($tipo, 'queso')) {
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

                $textoSolicitud = 'Número de solicitud: ' . ($solicitud->numero_solicitud ?? '---');
                $sheet->setCellValue('F16', $textoSolicitud);
                $sheet->mergeCells('F16:M18');

                $sheet->setCellValue("K{$filaResultados}", $muestra->ensayos->first()->pivot->codigo_trazabilidad ?? '-----');
                $sheet->getStyle("K{$filaResultados}")->applyFromArray($centrado);
            }

            // Datos fijos
            $sheet->setCellValue("A{$filaResultados}", $muestra->codigo_cliente ?? '');
            $sheet->setCellValue("B{$filaResultados}", $muestra->created_at ?? '');
            $sheet->setCellValue("C{$filaResultados}", $muestra->ensayos->first()->pivot->fecha_analisis ?? '');

            // Tabla inferior
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

            // Guardar archivo
            $fileName = 'Resultados_' . $solicitud->numero_solicitud . '_' . ucfirst($muestra->tipoMuestra->nombre) . '.xlsx';
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
            $filePath = "{$tempDir}/{$fileName}";

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);
            $archivosGenerados[] = $filePath;
        }

        // 📦 Crear ZIP si hay varios
        if (count($archivosGenerados) > 1) {
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
            $archivoFinal = $zipPath;
        } else {
            $archivoFinal = $archivosGenerados[0] ?? null;
        }

        if (!$archivoFinal || !file_exists($archivoFinal)) {
            return back()->with('error', 'No se pudo generar el archivo para enviar.');
        }

        // 📧 Enviar correo
        $correoCliente = $solicitud->cliente->correo_electronico;

        try {
            Mail::to($correoCliente)->send(new \App\Mail\ResultadoMailable($solicitud, $archivoFinal));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al enviar el correo: ' . $e->getMessage());
        }

        // 🧹 Eliminar archivo temporal
        @unlink($archivoFinal);

        return back()->with('success', '📨 Resultados enviados correctamente a ' . $correoCliente);
    }
}
