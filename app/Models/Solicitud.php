<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    protected $table = 'solicitudes';
    use HasFactory;

    protected $fillable = [
        'numero_solicitud',
        'cliente_id',
        'fecha_solicitud',
        'entrega_resultados',
        'declaracion_conformidad',
        'aprobada',
        'observaciones'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function muestras()
    {
        return $this->hasMany(Muestra::class);
    }


    public function calcularEstadoGlobal(): string
    {
        $muestras = $this->muestras;

        if ($muestras->isEmpty()) {
            return 'pendiente';
        }

        $totalMuestras = $muestras->count();
        $muestrasFinalizadas = 0;
        $muestrasEnProceso = 0;

        foreach ($muestras as $muestra) {
            $estadoMuestra = $muestra->calcularEstado();

            if ($estadoMuestra === 'finalizada') {
                $muestrasFinalizadas++;
            } elseif ($estadoMuestra === 'en_proceso') {
                $muestrasEnProceso++;
            }
        }

        // Lógica del estado global
        if ($muestrasFinalizadas === $totalMuestras) {
            return 'finalizada';
        } elseif ($muestrasEnProceso > 0 || $muestrasFinalizadas > 0) {
            return 'en_proceso';
        }

        return 'pendiente';
    }

    /**
     * Accessor para estado_global (calculado en tiempo real)
     */
    public function getEstadoGlobalAttribute(): string
    {
        return $this->calcularEstadoGlobal();
    }

    
}
