<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_controles', function (Blueprint $table) {
            $table->boolean('notificado_3_dias')->default(false)->after('data_conclusao');
            $table->boolean('notificado_no_dia')->default(false)->after('notificado_3_dias');
            $table->boolean('notificado_vencido')->default(false)->after('notificado_no_dia');
        });
    }

    public function down(): void
    {
        Schema::table('item_controles', function (Blueprint $table) {
            $table->dropColumn([
                'notificado_3_dias',
                'notificado_no_dia',
                'notificado_vencido',
            ]);
        });
    }
};
