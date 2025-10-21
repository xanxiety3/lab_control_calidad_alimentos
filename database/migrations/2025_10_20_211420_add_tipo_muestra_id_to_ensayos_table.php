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
        Schema::table('ensayos', function (Blueprint $table) {
        $table->foreignId('tipo_muestra_id')
              ->nullable()
              ->constrained('tipo_muestras')
              ->nullOnDelete();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ensayos', function (Blueprint $table) {
            //
        });
    }
};
