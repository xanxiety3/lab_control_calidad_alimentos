<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('muestra_ensayo', function (Blueprint $table) {
            $table->date('fecha_analisis')->nullable()->after('ensayo_id');
            $table->string('resultado')->nullable()->after('fecha_analisis');
            $table->string('unidad_medida')->nullable()->after('resultado');
            $table->string('codigo_trazabilidad')->nullable()->after('unidad_medida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('muestra_ensayo', function (Blueprint $table) {
            $table->dropColumn(['fecha_analisis', 'resultado', 'unidad_medida', 'codigo_trazabilidad']);
        });
    }
};
