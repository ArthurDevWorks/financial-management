<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_indicators', function (Blueprint $table) {
            $table->string('logo_url', 500)->nullable()->after('asset_type');
        });

        Schema::table('fii_indicators', function (Blueprint $table) {
            $table->string('logo_url', 500)->nullable()->after('book_value_per_share');
        });
    }

    public function down(): void
    {
        Schema::table('asset_indicators', function (Blueprint $table) {
            $table->dropColumn('logo_url');
        });

        Schema::table('fii_indicators', function (Blueprint $table) {
            $table->dropColumn('logo_url');
        });
    }
};
