<?php

namespace App\Http\Controllers;

use App\Mail\ResultadoMailable;
use App\Models\EnsayoFisicoquimico;
use App\Models\EnsayoMicrobiologia;
use App\Models\Muestra;
use App\Models\MuestraEnsayo;
use App\Models\Solicitud;
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
