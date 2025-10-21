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
            ->withPivot('observaciones')
            ->withTimestamps();
    }

    public function rechazos()
    {
        return $this->hasMany(RechazoMuestra::class);
    }
    public function tipoMuestra()
    {
        return $this->belongsTo(TipoMuestra::class);
    }
}
