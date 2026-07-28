<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Se a chave estrangeira existir, removemos ela primeiro antes de truncar
        if (Schema::hasColumn('investiment_valuations', 'investiment_id')) {
            Schema::table('investiment_valuations', function (Blueprint $table) {
                $table->dropForeign(['investiment_id']);
            });
        }

        // Truncar para limpar registros antigos incompatíveis com a nova FK
        \Illuminate\Support\Facades\DB::table('investiment_valuations')->truncate();

        // 1. Modificar a tabela investiment_valuations
        Schema::table('investiment_valuations', function (Blueprint $table) {
            if (Schema::hasColumn('investiment_valuations', 'investiment_id')) {
                $table->dropColumn('investiment_id');
            }

            if (Schema::hasColumn('investiment_valuations', 'projected_cash_flows')) {
                $table->dropColumn('projected_cash_flows');
            }

            if (Schema::hasColumn('investiment_valuations', 'summary')) {
                $table->dropColumn('summary');
            }

            // Adicionar a nova chave estrangeira para assets se não existir
            if (!Schema::hasColumn('investiment_valuations', 'asset_id')) {
                $table->foreignId('asset_id')->after('id')->constrained('assets')->cascadeOnDelete();
            }
        });

        // 2. Dropar as tabelas obsoletas
        Schema::dropIfExists('investiments');
        Schema::dropIfExists('asset_indicators');
        Schema::dropIfExists('fii_indicators');
    }

    public function down(): void
    {
        // Para reverter, precisaríamos recriar as tabelas obsoletas e reverter as colunas
        Schema::table('investiment_valuations', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
            $table->dropColumn('asset_id');
        });
    }
};
