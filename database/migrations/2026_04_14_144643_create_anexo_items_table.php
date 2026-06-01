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
        Schema::create('anexo_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_controle_id')->constrained()->cascadeOnDelete();

            $table->string('nome_arquivo');
            $table->string('caminho_arquivo');
            $table->string('tipo_arquivo')->nullable();
            $table->integer('tamanho')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anexo_items');
    }
};
