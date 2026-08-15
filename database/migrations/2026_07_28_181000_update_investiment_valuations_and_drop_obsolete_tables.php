<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Adicionar asset_id (nullable) para poder migrar os dados antigos
        Schema::table('investiment_valuations', function (Blueprint $table) {
            if (Schema::hasColumn('investiment_valuations', 'investiment_id')) {
                $table->dropForeign(['investiment_id']);
            }
        });

        if (! Schema::hasColumn('investiment_valuations', 'asset_id')) {
            Schema::table('investiment_valuations', function (Blueprint $table) {
                $table->unsignedBigInteger('asset_id')->nullable()->after('id');
            });
        }

        // 2. Migrar os registros antigos: vincular pelo ticker do investiment
        $tickerByInvestiment = DB::table('investiments')
            ->pluck('name', 'id')
            ->map(fn ($name) => strtoupper(trim((string) $name)))
            ->all();

        $assetIdByTicker = DB::table('assets')
            ->get(['id', 'ticker'])
            ->mapWithKeys(fn ($a) => [strtoupper(trim((string) $a->ticker)) => $a->id])
            ->all();

        DB::table('investiment_valuations')
            ->whereNull('asset_id')
            ->orderBy('id')
            ->each(function (object $valuation) use ($tickerByInvestiment, $assetIdByTicker): void {
                $ticker = $tickerByInvestiment[$valuation->investiment_id] ?? null;

                if ($ticker !== null && isset($assetIdByTicker[$ticker])) {
                    DB::table('investiment_valuations')
                        ->where('id', $valuation->id)
                        ->update(['asset_id' => $assetIdByTicker[$ticker]]);
                }
            });

        // 3. Descartar apenas os registros sem correspondência (órfãos)
        DB::table('investiment_valuations')->whereNull('asset_id')->delete();

        // 4. Ajustar colunas e tornar asset_id obrigatório
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

            $table->unsignedBigInteger('asset_id')->nullable(false)->change();
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
        });

        // 5. Dropar as tabelas obsoletas
        Schema::dropIfExists('investiments');
        Schema::dropIfExists('asset_indicators');
        Schema::dropIfExists('fii_indicators');
    }

    public function down(): void
    {
        // Reverter a estrutura, recriando o mínimo necessário
        Schema::table('investiment_valuations', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
            $table->dropColumn('asset_id');
        });

        Schema::create('investiments', function (Blueprint $table) {
            $table->id();
            $table->string('type', 60);
            $table->string('name');
            $table->dateTime('dt_investment');
            $table->decimal('value', 15, 2);
            $table->integer('profitability');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
