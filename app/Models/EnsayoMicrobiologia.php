<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnsayoMicrobiologia extends Model
{
    use HasFactory;

    protected $table = 'ensayos_microbiologia';

    protected $fillable = [
        'muestra_ensayo_id',
        'dilucion1_c1',
        'dilucion1_c2',
        'dilucion2_c1',
        'dilucion2_c2',
        'resultado',
        'unidad',
        'updated_at',
    ];

    /** 🔗 Relación con muestra_ensayo */
    public function muestraEnsayo()
    {
        return $this->belongsTo(muestraEnsayo::class, 'muestra_ensayo_id');
    }

    /**
     * 🧮 Calcula el resultado de microbiología
     * Fórmula: (Suma de todas las diluciones / 4) * 1000
     */
    public function calcularResultado(): float
    {
        $suma = (
            ($this->dilucion1_c1 ?? 0) +
            ($this->dilucion1_c2 ?? 0) +
            ($this->dilucion2_c1 ?? 0) +
            ($this->dilucion2_c2 ?? 0)
        );

        $resultado = ($suma / 4) * 1000;
        $this->resultado = round($resultado, 2);

        return $this->resultado;
    }

    /**
     * 💾 Calcula y guarda automáticamente
     */
    public function calcularYGuardar()
    {
        $this->calcularResultado();
        $this->save();
    }
}
