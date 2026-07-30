<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fii_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('ticker', 20)->unique()->index();
            $table->string('name', 255)->nullable();
            $table->string('segment', 100)->nullable();
            $table->string('manager', 100)->nullable();

            // Preço e mercado
            $table->decimal('current_price', 14, 2)->nullable();
            $table->decimal('market_cap', 18, 2)->nullable();
            $table->decimal('volume_avg_30d', 18, 2)->nullable();
            $table->decimal('liquidity', 18, 2)->nullable();

            // Valuation
            $table->decimal('dividend_yield', 8, 4)->nullable(); // em %
            $table->decimal('p_vp', 10, 2)->nullable();
            $table->decimal('cap_rate', 8, 4)->nullable();

            // Indicadores
            $table->decimal('vacancy_rate', 8, 4)->nullable(); // em %
            $table->decimal('vacancy_financial', 8, 4)->nullable();
            $table->decimal('average_maturity', 8, 2)->nullable(); // meses
            $table->decimal('number_of_properties', 8, 0)->nullable();
            $table->decimal('rental_area', 14, 2)->nullable(); // m²
            $table->decimal('ffo_yield', 8, 4)->nullable(); // em %

            // Financeiro
            $table->decimal('net_income', 18, 2)->nullable();
            $table->decimal('revenue', 18, 2)->nullable();
            $table->decimal('dividends_per_share', 14, 6)->nullable();
            $table->decimal('net_worth', 18, 2)->nullable();
            $table->decimal('book_value_per_share', 14, 4)->nullable();

            // Metadados
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fii_indicators');
    }
};
