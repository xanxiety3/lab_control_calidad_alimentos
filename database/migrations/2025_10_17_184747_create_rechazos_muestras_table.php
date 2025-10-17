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
        Schema::create('rechazos_muestras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muestra_id')->constrained('muestras')->onDelete('cascade');
            $table->foreignId('criterio_id')->nullable()->constrained('criterios_aceptacion')->onDelete('set null');
            $table->text('motivo')->nullable();
            $table->timestamp('fecha_rechazo')->useCurrent();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('notificado_cliente')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rechazos_muestras');
    }
};
