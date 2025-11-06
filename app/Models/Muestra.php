<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Muestra extends Model
{
    use HasFactory;

    protected $fillable = [
        'solicitud_id',
        'codigo_cliente',
        'codigo_interno',
        'tipo_muestra_id',
        'cantidad',
        'condiciones',
        'estado'
    ];


    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function ensayos()
    {
        return $this->belongsToMany(Ensayo::class, 'muestra_ensayo')
            ->withPivot(['id', 'observaciones', 'fecha_analisis', 'resultado', 'unidad_medida', 'codigo_trazabilidad','estado'])
            ->withTimestamps();
    }

    public function muestraEnsayos()
    {
        return $this->hasMany(MuestraEnsayo::class);
    }


    public function rechazos()
    {
        return $this->hasMany(RechazoMuestra::class);
    }
    public function tipoMuestra()
    {
        return $this->belongsTo(TipoMuestra::class);
    }



    /**
     * Calcular el estado de la muestra basado en sus ensayos
     */
    public function calcularEstado(): string
    {
        $ensayos = $this->muestraEnsayos;

        if ($ensayos->isEmpty()) {
            return 'pendiente';
        }

        $totalEnsayos = $ensayos->count();
        $ensayosCompletados = $ensayos->whereNotNull('resultado')
            ->where('resultado', '!=', '')
            ->where('resultado', '!=', 0)
            ->count();

        if ($ensayosCompletados === 0) {
            return 'pendiente';
        } elseif ($ensayosCompletados === $totalEnsayos) {
            return 'finalizada';
        } else {
            return 'en_proceso';
        }
    }

    /**
     * Accessor para estado (calculado en tiempo real)
     */
    public function getEstadoAttribute(): string
    {
        return $this->calcularEstado();
    }
}
