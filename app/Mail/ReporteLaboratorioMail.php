<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Solicitud;

class ReporteLaboratorioMail extends Mailable
{
    use Queueable, SerializesModels;

    public $solicitud;
    public $rutaArchivo;

    public function __construct(Solicitud $solicitud, $rutaArchivo)
    {
        $this->solicitud = $solicitud;
        $this->rutaArchivo = $rutaArchivo;
    }

    public function build()
    {
        return $this->subject('📊 Reporte de Resultados del Laboratorio')
            ->view('resultados.emailReporte')
            ->attach($this->rutaArchivo, [
                'as' => 'Resultados_' . $this->solicitud->numero_solicitud . '.xlsx',
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    }
}
