<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParametrosCorrecion extends Model
{
    protected $table = "parametros_correcion";
    protected $fillable = [
        'tipo',
        'valor_1',
        'valor_2',
        'valor_3',
         'created_at',
        'updated_at'
    ];
public $timestamps = false;

    public function laboratorio()
    {
        return $this->belongsTo(Laboratorio::class);
    }
}
