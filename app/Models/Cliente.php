<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'persona_id',
        'direccion',
        'municipio_id',
        'departamento_id',
        'telefono',
        'correo_electronico',
        'tipo_cliente'
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class);
    }
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }
    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }
}
