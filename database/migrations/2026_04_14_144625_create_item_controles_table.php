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
        Schema::create('item_controles', function (Blueprint $table) {
            $table->id();

            $table->string('titulo');
            $table->text('descricao')->nullable();

            $table->string('tipo');
            $table->string('status')->default('pendente');

            $table->date('data_vencimento')->nullable();
            $table->date('data_conclusao')->nullable();

            $table->text('observacao')->nullable();

            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('responsavel_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamp('ultimo_alerta_enviado_em')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_controles');
    }
};
