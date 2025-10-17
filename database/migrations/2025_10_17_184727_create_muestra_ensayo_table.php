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
        Schema::create('muestra_ensayo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muestra_id')->constrained('muestras')->onDelete('cascade');
            $table->foreignId('ensayo_id')->constrained('ensayos')->onDelete('cascade');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muestra_ensayo');
    }
};
