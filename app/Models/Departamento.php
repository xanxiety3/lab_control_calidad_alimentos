<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'codigo'];

    public function municipios()
    {
        return $this->hasMany(Municipio::class);
    }
    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
}
