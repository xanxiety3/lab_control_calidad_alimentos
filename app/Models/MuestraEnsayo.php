<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MuestraEnsayo extends Model
{
    protected $table = 'muestra_ensayo';

    protected $fillable = [
        'muestra_id',
        'ensayo_id',
        'fecha_analisis',
        'resultado',
        'unidad_medida',
        'codigo_trazabilidad',
        'observaciones',
    ];

    /** 🔗 Relación con la muestra */
    public function muestra()
    {
        return $this->belongsTo(Muestra::class);
    }

    /** 🔗 Relación con el ensayo */
    public function ensayo()
    {
        return $this->belongsTo(Ensayo::class);
    }

    /** 🧫 Relación con los ensayos microbiológicos */
    public function microbiologia()
    {
        return $this->hasOne(EnsayoMicrobiologia::class, 'muestra_ensayo_id');
    }

    /** ⚗️ Relación con los ensayos fisicoquímicos */
    public function fisicoquimico()
    {
        return $this->hasOne(EnsayoFisicoquimico::class, 'muestra_ensayo_id');
    }
}
