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
        Schema::table('investiments', function (Blueprint $table) {
            $table->string('logo_url')->nullable()->after('liquidity');
            $table->timestamp('last_price_fetched_at')->nullable()->after('logo_url');
        });
    }

    public function down(): void
    {
        Schema::table('investiments', function (Blueprint $table) {
            $table->dropColumn(['logo_url', 'last_price_fetched_at']);
        });
    }
};
