<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('ticker', 20)->unique()->index();
            $table->string('name', 255)->nullable();
            $table->string('sector', 100)->nullable();
            $table->string('subsector', 100)->nullable();
            $table->string('segment', 100)->nullable();

            // Preço e mercado
            $table->decimal('current_price', 14, 2)->nullable();
            $table->decimal('market_cap', 18, 2)->nullable();
            $table->decimal('enterprise_value', 18, 2)->nullable();
            $table->decimal('volume_avg_30d', 18, 2)->nullable();

            // Valuation
            $table->decimal('dividend_yield', 8, 4)->nullable(); // em %
            $table->decimal('price_to_earnings', 10, 2)->nullable();
            $table->decimal('price_to_book', 10, 2)->nullable();
            $table->decimal('ev_to_ebitda', 10, 2)->nullable();
            $table->decimal('price_to_sales', 10, 2)->nullable();
            $table->decimal('price_to_assets', 10, 2)->nullable();
            $table->decimal('price_to_cash_flow', 10, 2)->nullable();

            // Rentabilidade
            $table->decimal('roe', 8, 4)->nullable(); // em %
            $table->decimal('roa', 8, 4)->nullable(); // em %
            $table->decimal('profit_margin', 8, 4)->nullable(); // em %
            $table->decimal('ebitda_margin', 8, 4)->nullable(); // em %
            $table->decimal('gross_margin', 8, 4)->nullable(); // em %

            // Saúde financeira
            $table->decimal('debt_to_ebitda', 10, 2)->nullable();
            $table->decimal('net_debt_to_ebitda', 10, 2)->nullable();
            $table->decimal('current_liquidity', 10, 2)->nullable();
            $table->decimal('payout', 8, 4)->nullable(); // em %

            // Dados financeiros
            $table->decimal('net_income', 18, 2)->nullable();
            $table->decimal('revenue', 18, 2)->nullable();
            $table->decimal('free_cash_flow', 18, 2)->nullable();
            $table->decimal('dividends_per_share', 14, 4)->nullable();
            $table->decimal('earnings_per_share', 14, 4)->nullable();
            $table->decimal('book_value_per_share', 14, 4)->nullable();
            $table->bigInteger('total_shares')->nullable();

            // Metadados
            $table->string('asset_type', 20)->default('stock'); // stock, fii, bdr, etf
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_indicators');
    }
};
