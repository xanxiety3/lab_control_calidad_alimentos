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
        Schema::create('criterios_aceptacion', function (Blueprint $table) {
            $table->id();
            $table->string('criterio');
            $table->text('condicion_aceptacion')->nullable();
            $table->text('condicion_rechazo')->nullable();
            $table->string('tipo_muestra')->nullable(); // leche, queso, ambos
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criterios_aceptacion');
    }
};
