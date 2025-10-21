<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ensayo;
use App\Models\TipoMuestra;

class EnsayoSeeder extends Seeder
{
    public function run(): void
    {
        // Crear tipos de muestra si no existen
        $lecheCruda = TipoMuestra::firstOrCreate(['nombre' => 'Leche cruda']);
        $quesoFresco = TipoMuestra::firstOrCreate(['nombre' => 'Queso fresco']);

        $ensayos = [
            // 🥛 Leche cruda
            [
                'nombre' => 'Aerobios mesófilos',
                'tipo_muestra_id' => $lecheCruda->id,
                'unidad_medida' => 'UFC/mL',
                'intervalo_medicion' => '30  a 3000,000 UFC/mL',
                'metodo_norma' => 'ISO 4833-I:2013/amd 1:2022',
                'activo' => true,
            ],
            [
                'nombre' => 'Determinación de grasa',
                'tipo_muestra_id' => $lecheCruda->id,
                'unidad_medida' => 'g/100 g',
                'intervalo_medicion' => '1,00 g/100 g a 7,00 g/100 g',
                'metodo_norma' => 'AOAC 2000.18. 22 Ed. 22st, 2023',
                'activo' => true,
            ],
            [
                'nombre' => 'Determinación de solidos totales',
                'tipo_muestra_id' => $lecheCruda->id,
                'unidad_medida' => 'g/100 g',
                'intervalo_medicion' => '9 g/100 g a 16 g/100 g',
                'metodo_norma' => 'NTC: 4979:2001',
                'activo' => true,
            ],
            [
                'nombre' => 'Determinación de densidad',
                'tipo_muestra_id' => $lecheCruda->id,
                'unidad_medida' => 'g/mL',
                'intervalo_medicion' => '1,025 a 1,035 g/ml',
                'metodo_norma' => 'AOAC 925.22 Ed. 22st, 2023',
                'activo' => true,
            ],

            // 🧀 Queso fresco
            [
                'nombre' => 'Coliformes totales',
                'tipo_muestra_id' => $quesoFresco->id,
                'unidad_medida' => 'UFC/g',
                'intervalo_medicion' => '30  a 3000,000 UFC/g',
                'metodo_norma' => 'NTC 4458:2018',
                'activo' => true,
            ],
            [
                'nombre' => 'E. coli',
                'tipo_muestra_id' => $quesoFresco->id,
                'unidad_medida' => 'UFC/g',
                'intervalo_medicion' => '30  a 3000,000 UFC/g',
                'metodo_norma' => 'NTC 4458:2018',
                'activo' => true,
            ],
            [
                'nombre' => 'Staphylococcu coagulasa (+)',
                'tipo_muestra_id' => $quesoFresco->id,
                'unidad_medida' => 'UFC/g',
                'intervalo_medicion' => '30  a 3000,000 UFC/g',
                'metodo_norma' => 'ISO 6888-1:2021',
                'activo' => true,
            ],
            [
                'nombre' => 'Mohos y levaduras',
                'tipo_muestra_id' => $quesoFresco->id,
                'unidad_medida' => 'UFC/g',
                'intervalo_medicion' => '30  a 3000,000 UFC/g',
                'metodo_norma' => 'ISO 21257-2:2008',
                'activo' => true,
            ],
            [
                'nombre' => 'Determinación de grasa',
                'tipo_muestra_id' => $quesoFresco->id,
                'unidad_medida' => 'g/100 g',
                'intervalo_medicion' => '5 g/100 g a 35 g/100 g',
                'metodo_norma' => 'ISO 3433:2008',
                'activo' => true,
            ],
            [
                'nombre' => 'Determinación de solidos totales',
                'tipo_muestra_id' => $quesoFresco->id,
                'unidad_medida' => 'g/100 g',
                'intervalo_medicion' => '40 g/100 g a 62 g/100 g',
                'metodo_norma' => 'NTC: 4979:2001',
                'activo' => true,
            ],
            [
                'nombre' => 'Determinación de humedad',
                'tipo_muestra_id' => $quesoFresco->id,
                'unidad_medida' => 'g/100 g',
                'intervalo_medicion' => '38 g/100 g a 60 g/100 g',
                'metodo_norma' => 'Cálculo por diferencia',
                'activo' => true,
            ],
        ];

        foreach ($ensayos as $ensayo) {
            Ensayo::firstOrCreate([
                'nombre' => $ensayo['nombre'],
                'tipo_muestra_id' => $ensayo['tipo_muestra_id'],
            ], $ensayo);
        }
    }
}
