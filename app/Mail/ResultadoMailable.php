<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Solicitud;

class ResultadoMailable extends Mailable
{
    public $solicitud;
    public $archivo;

    public function __construct($solicitud, string $archivo)
    {
        $this->solicitud = $solicitud;
        $this->archivo = $archivo;
    }

    public function build()
    {
        $correo = $this->subject('Resultados – Solicitud ' . $this->solicitud->numero_solicitud)
            ->view('emails.resultados');

        if (is_string($this->archivo) && file_exists($this->archivo)) {
            $correo->attach($this->archivo, [
                'as' => basename($this->archivo),
                'mime' => mime_content_type($this->archivo),
            ]);
        }

        return $correo;
    }
}
