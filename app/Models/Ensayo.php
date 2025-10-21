<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ensayo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tipo_muestra_id',
        'unidad_medida',
        'intervalo_medicion',
        'metodo_norma',
        'activo'
    ];


    public function muestras()
    {
        return $this->belongsToMany(Muestra::class, 'muestra_ensayo')
            ->withPivot('observaciones')
            ->withTimestamps();
    }
    public function tipoMuestra()
    {
        return $this->belongsTo(TipoMuestra::class);
    }
}
