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
        Schema::create('ensayos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo_muestra')->nullable(); // leche, queso, ambos
            $table->string('unidad_medida')->nullable();
            $table->string('intervalo_medicion')->nullable();
            $table->string('metodo_norma')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ensayos');
    }
};
