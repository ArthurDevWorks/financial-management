<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('ticker', 20)->unique()->index();
            $table->string('name', 255)->nullable();
            $table->string('asset_type', 20)->default('stock'); // stock, fii

            // Setor / Atuação
            $table->string('sector', 100)->nullable();
            $table->string('subsector', 100)->nullable();
            $table->string('segment', 100)->nullable();

            // Cotação e volume
            $table->decimal('current_price', 14, 2)->nullable();
            $table->decimal('market_cap', 18, 2)->nullable();
            $table->decimal('enterprise_value', 18, 2)->nullable();
            $table->decimal('volume_avg_30d', 18, 2)->nullable();

            // Indicadores de Valuation & Rentabilidade (Ações/Geral)
            $table->decimal('dividend_yield', 8, 4)->nullable(); // %
            $table->decimal('price_to_earnings', 10, 2)->nullable(); // P/L
            $table->decimal('price_to_book', 10, 2)->nullable();     // P/VP
            $table->decimal('ev_to_ebitda', 10, 2)->nullable();
            $table->decimal('price_to_sales', 10, 2)->nullable();
            $table->decimal('price_to_assets', 10, 2)->nullable();
            $table->decimal('price_to_cash_flow', 10, 2)->nullable();
            $table->decimal('roe', 8, 4)->nullable(); // %
            $table->decimal('roa', 8, 4)->nullable(); // %
            $table->decimal('profit_margin', 8, 4)->nullable(); // %
            $table->decimal('ebitda_margin', 8, 4)->nullable(); // %
            $table->decimal('gross_margin', 8, 4)->nullable(); // %

            // Saúde Financeira & Estrutura (Ações)
            $table->decimal('debt_to_ebitda', 10, 2)->nullable();
            $table->decimal('net_debt_to_ebitda', 10, 2)->nullable();
            $table->decimal('current_liquidity', 10, 2)->nullable();
            $table->decimal('payout', 8, 4)->nullable(); // %

            // Dados Financeiros Básicos
            $table->decimal('net_income', 18, 2)->nullable();
            $table->decimal('revenue', 18, 2)->nullable();
            $table->decimal('free_cash_flow', 18, 2)->nullable();
            $table->decimal('dividends_per_share', 14, 4)->nullable();
            $table->decimal('earnings_per_share', 14, 4)->nullable();
            $table->decimal('book_value_per_share', 14, 4)->nullable();
            $table->bigInteger('total_shares')->nullable();

            // Perfil
            $table->string('logo_url', 500)->nullable();
            $table->text('long_business_summary')->nullable();
            $table->string('website', 255)->nullable();
            $table->integer('full_time_employees')->nullable();

            // Indicadores Específicos de FIIs
            $table->decimal('p_vp', 10, 2)->nullable(); // Duplicado de price_to_book para facilitar uso específico de FIIs
            $table->decimal('cap_rate', 8, 4)->nullable();
            $table->decimal('vacancy_rate', 8, 4)->nullable(); // %
            $table->decimal('vacancy_financial', 8, 4)->nullable();
            $table->decimal('average_maturity', 8, 2)->nullable(); // meses
            $table->decimal('number_of_properties', 8, 0)->nullable();
            $table->decimal('rental_area', 14, 2)->nullable(); // m²
            $table->decimal('ffo_yield', 8, 4)->nullable(); // %
            $table->decimal('net_worth', 18, 2)->nullable(); // patrimônio líquido
            $table->string('manager', 100)->nullable();

            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
