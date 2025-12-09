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
        Schema::create('parametros_correcion', function (Blueprint $table) {
            $table->id();
             $table->enum('tipo', ['temperatura','humedad']);
             $table->decimal('valor_1',8,2)->nullable();
             $table->decimal('valor_2',8,2)->nullable();
             $table->decimal('valor_3',8,2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametros_correcion');
    }
};
