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

        Schema::create('ensayos_microbiologia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muestra_ensayo_id')
                ->constrained('muestra_ensayo')
                ->onDelete('cascade');

            // DILUCIÓN 1
         
            $table->decimal('dilucion1_c1', 10, 2)->nullable();
            $table->decimal('dilucion1_c2', 10, 2)->nullable();

            // DILUCIÓN 2
     
            $table->decimal('dilucion2_c1', 10, 2)->nullable();
            $table->decimal('dilucion2_c2', 10, 2)->nullable();

            // RESULTADO
            $table->decimal('resultado', 10, 2)->nullable();
            $table->string('unidad')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ensayos_microbiologia');
    }
};
