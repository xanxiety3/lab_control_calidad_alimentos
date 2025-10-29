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
        Schema::create('ensayos_fisicoquimicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muestra_ensayo_id')
                ->constrained('muestra_ensayo')
                ->onDelete('cascade');

            $table->enum('tipo', ['grasa', 'solidos_totales', 'humedad', 'densidad']);

            // GRASA
            $table->decimal('replica1_a', 10, 2)->nullable();
            $table->decimal('replica1_b', 10, 2)->nullable();
            $table->decimal('replica2_a', 10, 2)->nullable();
            $table->decimal('replica2_b', 10, 2)->nullable();
            $table->decimal('resultado_grasa', 10, 2)->nullable();
            $table->string('unidad_grasa')->nullable();

            // SÓLIDOS TOTALES / HUMEDAD
            $table->decimal('replica1_m0', 10, 2)->nullable();
            $table->decimal('replica1_m1', 10, 2)->nullable();
            $table->decimal('replica1_m2', 10, 2)->nullable();
            $table->decimal('resultado_porcentaje', 10, 2)->nullable();

            // DENSIDAD (manual)
            $table->text('densidad')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ensayos_fisicoquimicos');
    }
};
