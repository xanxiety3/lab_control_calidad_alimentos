<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Ensayo;
use App\Models\Muestra;
use App\Models\Solicitud;
use App\Models\TipoMuestra;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RemisionController extends Controller
{
    public function create()
    {
        $clientes = Cliente::with('persona')->get();
        $ensayos = Ensayo::where('activo', true)->get();
        $departamentos = Departamento::all();
        $tipoMuestras = TipoMuestra::all();
        $codigoSolicitud = '25-048'; // ejemplo dinámico
        return view('remisiones.create', compact('clientes', 'ensayos', 'departamentos', 'tipoMuestras', 'codigoSolicitud'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'entrega_resultados' => 'required|in:correo,personal,ambos',
            'muestras' => 'required|array|min:1',
            'muestras.*.tipo_muestra_id' => 'required|exists:tipo_muestras,id',
            'muestras.*.cantidad' => 'required',
            'muestras.*.ensayos' => 'required|array|min:1',
        ]);

        // --- Crear solicitud automáticamente ---
        $anio = date('y');
        $ultimo = Solicitud::where('numero_solicitud', 'like', "$anio-%")
            ->orderByDesc('id')
            ->value('numero_solicitud');

        $nuevoNumero = $ultimo
            ? str_pad((int) substr($ultimo, 3) + 1, 3, '0', STR_PAD_LEFT)
            : '001';

        $numeroSolicitud = "{$anio}-{$nuevoNumero}";

        $solicitud = Solicitud::create([
            'numero_solicitud' => $numeroSolicitud,
            'cliente_id' => $request->cliente_id,
            'fecha_solicitud' => now(),
            'entrega_resultados' => $request->entrega_resultados,
            'declaracion_conformidad' => false,
            'aprobada' => true,
            'observaciones' => $request->observaciones,
        ]);

        // --- Crear muestras asociadas ---
        $letras = range('A', 'Z');
        foreach ($request->muestras as $i => $muestraData) {
            $codigoInterno = $letras[$i] . '-' . substr($numeroSolicitud, 3);

            $muestra = Muestra::create([
                'solicitud_id' => $solicitud->id,
                'codigo_cliente' => $muestraData['codigo_cliente'] ?? null,
                'codigo_interno' => $codigoInterno,
                'tipo_muestra_id' => $muestraData['tipo_muestra_id'],
                'cantidad' => $muestraData['cantidad'],
                'condiciones' => $muestraData['condiciones'] ?? null,
                'estado' => 'pendiente',
            ]);

            $muestra->ensayos()->sync($muestraData['ensayos']);
        }

        return redirect()->route('dashboard.recepcion')->with('success', 'Remisión creada correctamente.');
    }





    public function exportar($id)
    {
        $solicitud = Solicitud::with(['cliente.persona', 'muestras.ensayos'])->findOrFail($id);

        $templatePath = storage_path('app/plantillas/formatoSolicitud.xlsx');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // 🔹 Fecha
        $fecha = \Carbon\Carbon::parse($solicitud->created_at);
        $sheet->setCellValue('C7', $fecha->year);
        $sheet->setCellValue('D7', $fecha->format('m'));
        $sheet->setCellValue('E7', $fecha->format('d'));

        // 🔹 Número de solicitud
        $sheet->setCellValue('K6', $solicitud->numero_solicitud);
        $sheet->mergeCells('K6:M7');

        // 🔹 Cliente
        $cliente = $solicitud->cliente;
        $persona = $cliente->persona;

        $sheet->setCellValue('A11', $persona->nombre_completo ?? '');
        $sheet->mergeCells('A11:E12');

        $sheet->setCellValue('F11', $persona->numero_documento ?? '');
        $sheet->mergeCells('F11:I12');

        $sheet->setCellValue('J11', $cliente->municipio->nombre ?? '');
        $sheet->mergeCells('J11:M12');

        $sheet->setCellValue('A15', $cliente->direccion ?? '');
        $sheet->mergeCells('A15:E16');

        $sheet->setCellValue('F15', $cliente->telefono ?? '');
        $sheet->mergeCells('F15:I16');

        $sheet->setCellValue('J15', $cliente->correo_electronico ?? '');
        $sheet->mergeCells('J15:M16');

        // 🔹 Tipo de cliente
        if ($cliente->tipo_cliente === 'interno') {
            $sheet->setCellValue('B19', 'X');
        } elseif ($cliente->tipo_cliente === 'externo') {
            $sheet->setCellValue('C19', 'X');
        }

        // 🔹 Entrega de resultados
        if ($solicitud->entrega_resultados === 'personal') {
            $sheet->setCellValue('G19', 'X');
        } elseif ($solicitud->entrega_resultados === 'correo') {
            $sheet->setCellValue('H19', 'X');
        } elseif ($solicitud->entrega_resultados === 'ambos') {
            $sheet->setCellValue('I19', 'X');
        }

        // 🔹 Declaración de conformidad
        $sheet->setCellValue('M19', 'X');


        // 🔹 Sección de muestras (encabezados en fila 23 y 24, datos desde fila 25)
        $muestras = $solicitud->muestras;
        $countMuestras = $muestras->count();
        $baseRow = 23;
        $subHeaderRow = $baseRow + 1;

        if ($countMuestras === 0) {
            return back()->with('error', 'No hay muestras registradas para esta solicitud.');
        }

        // Detectar tipos de muestra
        $tieneLeche = $muestras->contains(fn($m) => str_contains(strtolower($m->tipoMuestra->nombre ?? ''), 'leche'));
        $tieneQueso  = $muestras->contains(fn($m) => str_contains(strtolower($m->tipoMuestra->nombre ?? ''), 'queso'));

        // Estilos
        $styleHeader = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'font' => ['bold' => true],
        ];

        $styleRow = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        // -- Encabezados
        $sheet->setCellValue("G{$baseRow}", 'ENSAYO SOLICITADO');
        $sheet->mergeCells("G{$baseRow}:K{$baseRow}");
        $sheet->getStyle("G{$baseRow}:K{$baseRow}")->applyFromArray($styleHeader);

        if ($tieneLeche && $tieneQueso) {
            $sheet->setCellValue("G{$subHeaderRow}", 'LECHE CRUDA');
            $sheet->mergeCells("G{$subHeaderRow}:I{$subHeaderRow}");
            $sheet->getStyle("G{$subHeaderRow}:I{$subHeaderRow}")->applyFromArray($styleHeader);

            $sheet->setCellValue("J{$subHeaderRow}", 'QUESO FRESCO');
            $sheet->mergeCells("J{$subHeaderRow}:K{$subHeaderRow}");
            $sheet->getStyle("J{$subHeaderRow}:K{$subHeaderRow}")->applyFromArray($styleHeader);
        } elseif ($tieneLeche && !$tieneQueso) {
            $sheet->setCellValue("G{$subHeaderRow}", 'LECHE CRUDA');
            $sheet->mergeCells("G{$subHeaderRow}:K{$subHeaderRow}");
            $sheet->getStyle("G{$subHeaderRow}:K{$subHeaderRow}")->applyFromArray($styleHeader);
        } elseif ($tieneQueso && !$tieneLeche) {
            $sheet->setCellValue("G{$subHeaderRow}", 'QUESO FRESCO');
            $sheet->mergeCells("G{$subHeaderRow}:K{$subHeaderRow}");
            $sheet->getStyle("G{$subHeaderRow}:K{$subHeaderRow}")->applyFromArray($styleHeader);
        }

        // -- Llenado de datos (desde fila 25)
        $row = $baseRow + 2;

        foreach ($muestras as $muestra) {
            $sheet->setCellValue("A{$row}", $muestra->codigo_cliente);
            $sheet->setCellValue("D{$row}", $muestra->codigo_interno);
            $sheet->setCellValue("F{$row}", $muestra->cantidad);

            $tipoNombre = strtolower($muestra->tipoMuestra->nombre ?? '');

            // --- CASO A: ambos tipos (leche y queso)
            if ($tieneLeche && $tieneQueso) {
                if (str_contains($tipoNombre, 'leche')) {
                    // fusionar G:I y escribir ensayo
                    $sheet->mergeCells("G{$row}:I{$row}");
                    $sheet->setCellValue("G{$row}", $muestra->ensayos->pluck('nombre')->join(', '));
                    // fusionar J:K y poner guiones
                    $sheet->mergeCells("J{$row}:K{$row}");
                    $sheet->setCellValue("J{$row}", '-------------------');
                } elseif (str_contains($tipoNombre, 'queso')) {
                    // fusionar J:K y escribir ensayo
                    $sheet->mergeCells("J{$row}:K{$row}");
                    $sheet->setCellValue("J{$row}", $muestra->ensayos->pluck('nombre')->join(', '));
                    // fusionar G:I y poner guiones
                    $sheet->mergeCells("G{$row}:I{$row}");
                    $sheet->setCellValue("G{$row}", '-------------------');
                } else {
                    // tipo no identificado
                    $sheet->mergeCells("G{$row}:K{$row}");
                    $sheet->setCellValue("G{$row}", '-------------------');
                }
            } else {
                // --- CASO B: solo un tipo (fusionar G:K para todos)
                $sheet->mergeCells("G{$row}:K{$row}");
                $sheet->setCellValue("G{$row}", $muestra->ensayos->pluck('nombre')->join(', '));
            }

            // Condiciones
            $sheet->setCellValue("L{$row}", $muestra->condiciones ?? '');
            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray($styleRow);

            // Centramos texto de ensayo y guiones
            $sheet->getStyle("G{$row}:K{$row}")
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $row++;
        }


        $sheet->setCellValue('L42', 'X');
        
        $sheet->setCellValue('L43', 'X');
        
        $sheet->setCellValue('L44', 'X');
    


        // 📤 Generar descarga directa sin guardar en disco
        $fileName = 'Solicitud_' . $solicitud->numero_solicitud . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Configurar headers de descarga directa
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
