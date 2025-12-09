<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroTemperatura extends Model
{
    protected $table = "registros_temperatura";
    protected $fillable = [
        'user_id',
        'tipo',
        'valor_original',
        'valor_corregido',
        'created_at',
        'updated_at'
    ];
}
