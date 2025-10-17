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
        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('departamento_id')->nullable()
                ->after('persona_id')
                ->constrained('departamentos')
                ->onDelete('set null');

            $table->foreignId('municipio_id')->nullable()
                ->after('departamento_id')
                ->constrained('municipios')
                ->onDelete('set null');

            $table->dropColumn(['ciudad', 'departamento']);
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
            $table->dropColumn('departamento_id');
            $table->dropForeign(['municipio_id']);
            $table->dropColumn('municipio_id');
            $table->string('ciudad')->nullable();
            $table->string('departamento')->nullable();
        });
    }
};
