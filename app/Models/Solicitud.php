<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    protected $table = 'solicitudes';
    use HasFactory;

    protected $fillable = [
        'numero_solicitud', 'cliente_id', 'fecha_solicitud',
        'entrega_resultados', 'declaracion_conformidad',
        'aprobada', 'observaciones'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function muestras()
    {
        return $this->hasMany(Muestra::class);
    }
}
