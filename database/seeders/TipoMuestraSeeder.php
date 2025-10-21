<?php

namespace Database\Seeders;

use App\Models\TipoMuestra;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoMuestraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Leche cruda', 'descripcion' => 'Muestra líquida tomada directamente del ordeño o tanque.'],
            ['nombre' => 'Queso fresco', 'descripcion' => 'Producto lácteo sólido obtenido por coagulación de leche.'],
        ];

        foreach ($tipos as $tipo) {
            TipoMuestra::create($tipo);
        }
    }
}
