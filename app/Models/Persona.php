<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
   
    use HasFactory;

    protected $fillable = [
        'tipo_persona', 'nombre_completo', 'razon_social',
        'tipo_documento', 'numero_documento'
    ];

    public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }

    // Getter dinámico para mostrar nombre según tipo
    public function getNombreIdentificacionAttribute()
    {
        return $this->tipo_persona === 'natural'
            ? $this->nombre_completo
            : $this->razon_social;
    }
}
