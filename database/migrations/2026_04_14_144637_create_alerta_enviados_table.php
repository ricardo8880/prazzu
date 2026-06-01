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
        Schema::create('alerta_enviados', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_controle_id')->constrained()->cascadeOnDelete();

            $table->string('tipo_alerta'); // 7_dias, 3_dias, vencido
            $table->string('destinatario')->nullable();
            $table->string('canal')->default('email');

            $table->timestamp('enviado_em')->nullable();
            $table->string('status_envio')->nullable();

            $table->text('mensagem')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerta_enviados');
    }
};
