<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoMuestra extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'activo'];

    public function ensayos()
    {
        return $this->hasMany(Ensayo::class);
    }

    public function muestras()
    {
        return $this->hasMany(Muestra::class);
    }
}
