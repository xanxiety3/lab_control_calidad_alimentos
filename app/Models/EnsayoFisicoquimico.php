<?php

                namespace App\Models;

                use Illuminate\Database\Eloquent\Factories\HasFactory;
                use Illuminate\Database\Eloquent\Model;

                class EnsayoFisicoquimico extends Model
                {
                    use HasFactory;

                    protected $table = 'ensayos_fisicoquimicos';

                    protected $fillable = [
                        'muestra_ensayo_id',
                        'tipo',
                        'replica1_a',
                        'replica1_b',
                        'replica2_a',
                        'replica2_b',
                        'resultado_grasa',
                        'unidad_grasa',
                        'replica1_m0',
                        'replica1_m1',
                        'replica1_m2',
                        'resultado_porcentaje',
                        'densidad',
                    ];

                    /** 🔗 Relación con muestra_ensayo */
                    public function muestraEnsayo()
                    {
                        return $this->belongsTo(MuestraEnsayo::class, 'muestra_ensayo_id');
                    }

                    /**
                    * 🧮 Cálculo para ensayo de GRASA
                    * Fórmula: promedio de ((B - A) réplica 1 y réplica 2)
                    */
                    public function calcularGrasa(): float
                    {
                        $r1 = ($this->replica1_b ?? 0) - ($this->replica1_a ?? 0);
                        $r2 = ($this->replica2_b ?? 0) - ($this->replica2_a ?? 0);

                        $promedio = ($r1 + $r2) / 2;
                        $this->resultado_grasa = round($promedio, 2);

                        return $this->resultado_grasa;
                    }

                    /**
                    * 💧 Cálculo para Sólidos Totales / Humedad
                    * Fórmula: ((m2 - m0) / (m1 - m0)) * 100
                    */
                    public function calcularPorcentaje(): float
                    {
                        $numerador = ($this->replica1_m2 ?? 0) - ($this->replica1_m0 ?? 0);
                        $denominador = ($this->replica1_m1 ?? 1) - ($this->replica1_m0 ?? 0);

                        if ($denominador == 0) {
                            $this->resultado_porcentaje = null;
                            return 0;
                        }

                        $resultado = ($numerador / $denominador) * 100;
                        $this->resultado_porcentaje = round($resultado, 2);

                        return $this->resultado_porcentaje;
                    }

                    /**
                    * 💾 Método general según tipo
                    */
                    public function calcularYGuardar()
                    {
                        switch ($this->tipo) {
                            case 'grasa':
                                $this->calcularGrasa();
                                break;
                            case 'solidos_totales':
                            case 'humedad':
                                $this->calcularPorcentaje();
                                break;
                        }

                        $this->save();
                    }
                }
