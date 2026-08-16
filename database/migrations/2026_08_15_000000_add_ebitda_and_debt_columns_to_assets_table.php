<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->decimal('ebitda', 18, 2)->nullable()->after('ebitda_margin');
            $table->decimal('net_debt', 18, 2)->nullable()->after('debt_to_ebitda');
            $table->decimal('gross_debt', 18, 2)->nullable()->after('net_debt');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['ebitda', 'net_debt', 'gross_debt']);
        });
    }
};
