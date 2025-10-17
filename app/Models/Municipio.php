<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
      use HasFactory;

    protected $fillable = ['departamento_id', 'nombre', 'codigo'];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
}
