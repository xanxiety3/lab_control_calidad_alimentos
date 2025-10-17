<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RechazoMuestra extends Model
{
   use HasFactory;

    protected $table = 'rechazos_muestras';

    protected $fillable = [
        'muestra_id', 'criterio_id', 'motivo',
        'fecha_rechazo', 'usuario_id', 'notificado_cliente'
    ];

    public function muestra()
    {
        return $this->belongsTo(Muestra::class);
    }

    public function criterio()
    {
        return $this->belongsTo(CriterioAceptacion::class, 'criterio_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
