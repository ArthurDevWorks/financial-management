<?php

use App\Enums\InvestmentAssetType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('investiments', function (Blueprint $table) {
            $table->dropForeign(['type']);
        });

        Schema::table('investiments', function (Blueprint $table) {
            $table->string('type', 60)->change();
            $table->decimal('quantity', 20, 8)->default(1)->after('type');
            $table->decimal('average_price', 15, 2)->nullable()->after('quantity');
            $table->decimal('current_balance', 15, 2)->nullable()->after('average_price');
            $table->decimal('profitability', 8, 2)->default(0)->change();
            $table->string('profitability_type', 30)->nullable()->after('profitability');
            $table->string('indexer', 30)->nullable()->after('profitability_type');
            $table->decimal('contracted_rate', 8, 4)->nullable()->after('indexer');
            $table->date('maturity_date')->nullable()->after('contracted_rate');
            $table->string('liquidity')->nullable()->after('maturity_date');
        });

        DB::table('investiments')
            ->orderBy('id')
            ->each(function (object $investiment): void {
                $legacyCategory = DB::table('categories')
                    ->where('id', $investiment->type)
                    ->first();

                $quantity = (float) ($investiment->quantity ?? 1);
                $averagePrice = $investiment->average_price ?? $investiment->value;
                $currentBalance = $investiment->current_balance ?? $investiment->value;
                $investedAmount = max(0, $quantity * (float) $averagePrice);
                $profitability = $investedAmount > 0
                    ? (((float) $currentBalance - $investedAmount) / $investedAmount) * 100
                    : 0;

                DB::table('investiments')
                    ->where('id', $investiment->id)
                    ->update([
                        'type' => InvestmentAssetType::fromLegacyCategoryName($legacyCategory?->name)->value,
                        'quantity' => $quantity > 0 ? $quantity : 1,
                        'average_price' => $averagePrice,
                        'current_balance' => $currentBalance,
                        'profitability' => round($profitability, 2),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investiments', function (Blueprint $table) {
            $table->dropColumn([
                'quantity',
                'average_price',
                'current_balance',
                'profitability_type',
                'indexer',
                'contracted_rate',
                'maturity_date',
                'liquidity',
            ]);
        });
    }
};
