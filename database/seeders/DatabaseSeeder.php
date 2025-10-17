<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /* =========================
         *  1️⃣  CREAR ROLES
         * ========================= */
        $roles = [
            ['nombre' => 'admin', 'descripcion' => 'Administrador del sistema'],
            ['nombre' => 'recepcion', 'descripcion' => 'Registro de solicitudes y muestras'],
            ['nombre' => 'analista', 'descripcion' => 'Registro de resultados de ensayos'],
            ['nombre' => 'gestor_tecnico', 'descripcion' => 'Aprobación o rechazo de muestras y solicitudes'],
            ['nombre' => 'consulta', 'descripcion' => 'Solo consulta de informes y resultados'],
        ];

        foreach ($roles as $rol) {
            Role::firstOrCreate(['nombre' => $rol['nombre']], $rol);
        }

        /* =========================
         *  2️⃣  CREAR PERMISOS
         * ========================= */
        $permisos = [
            // Recepción
            ['nombre' => 'crear_solicitud', 'descripcion' => 'Registrar solicitudes de análisis'],
            ['nombre' => 'registrar_muestra', 'descripcion' => 'Registrar muestras en el sistema'],
            // Analista
            ['nombre' => 'registrar_resultado', 'descripcion' => 'Registrar resultados de ensayos'],
            // Gestor técnico
            ['nombre' => 'aprobar_muestra', 'descripcion' => 'Aprobar muestras para análisis'],
            ['nombre' => 'rechazar_muestra', 'descripcion' => 'Rechazar muestras según criterios de aceptación'],
            // Consultas e informes
            ['nombre' => 'ver_informes', 'descripcion' => 'Consultar resultados e informes finales'],
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['nombre' => $permiso['nombre']], $permiso);
        }

        /* =========================
         *  3️⃣  ASIGNAR PERMISOS A ROLES
         * ========================= */
        $roleAdmin = Role::where('nombre', 'admin')->first();
        $roleRecepcion = Role::where('nombre', 'recepcion')->first();
        $roleAnalista = Role::where('nombre', 'analista')->first();
        $roleGestor = Role::where('nombre', 'gestor_tecnico')->first();
        $roleConsulta = Role::where('nombre', 'consulta')->first();

        $permisosAll = Permission::pluck('id')->toArray();
        $permisosRecepcion = Permission::whereIn('nombre', ['crear_solicitud', 'registrar_muestra'])->pluck('id')->toArray();
        $permisosAnalista = Permission::whereIn('nombre', ['registrar_resultado'])->pluck('id')->toArray();
        $permisosGestor = Permission::whereIn('nombre', ['aprobar_muestra', 'rechazar_muestra'])->pluck('id')->toArray();
        $permisosConsulta = Permission::whereIn('nombre', ['ver_informes'])->pluck('id')->toArray();

        // Sync relaciones (pivot role_permission)
        $roleAdmin->permissions()->sync($permisosAll);
        $roleRecepcion->permissions()->sync($permisosRecepcion);
        $roleAnalista->permissions()->sync($permisosAnalista);
        $roleGestor->permissions()->sync($permisosGestor);
        $roleConsulta->permissions()->sync($permisosConsulta);

        /* =========================
         *  4️⃣  CREAR USUARIO ADMIN
         * ========================= */
        $admin = User::firstOrCreate(
            ['email' => 'admin@lcca.com'],
            [
                'name' => 'Leidi Lizcano',
                'password' => Hash::make('admin123'),
                'role_id' => $roleAdmin->id,
            ]
        );

        /* =========================
         *  5️⃣  DEPARTAMENTOS Y MUNICIPIOS BASE
         * ========================= */
        $departamentos = [
            ['nombre' => 'La Guajira', 'codigo' => '44'],
            ['nombre' => 'Cesar', 'codigo' => '20'],
            ['nombre' => 'Magdalena', 'codigo' => '47'],
        ];

        foreach ($departamentos as $dep) {
            Departamento::firstOrCreate(['nombre' => $dep['nombre']], $dep);
        }

        $guajira = Departamento::where('nombre', 'La Guajira')->first();

        $municipios = [
            ['departamento_id' => $guajira->id, 'nombre' => 'Fonseca', 'codigo' => '44279'],
            ['departamento_id' => $guajira->id, 'nombre' => 'Riohacha', 'codigo' => '44001'],
            ['departamento_id' => $guajira->id, 'nombre' => 'San Juan del Cesar', 'codigo' => '44650'],
        ];

        foreach ($municipios as $mun) {
            Municipio::firstOrCreate(
                ['departamento_id' => $mun['departamento_id'], 'nombre' => $mun['nombre']],
                $mun
            );
        }

        $this->command->info('✅ Seeder ejecutado correctamente: roles, permisos, admin y ubicación base creados.');
    }
}
