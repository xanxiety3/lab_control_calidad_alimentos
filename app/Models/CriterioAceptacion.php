<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriterioAceptacion extends Model
{
    use HasFactory;

    protected $table = 'criterios_aceptacion';

    protected $fillable = [
        'criterio', 'condicion_aceptacion',
        'condicion_rechazo', 'tipo_muestra', 'activo'
    ];

    public function rechazos()
    {
        return $this->hasMany(RechazoMuestra::class, 'criterio_id');
    }
}
