<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_indicators', function (Blueprint $table) {
            $table->text('long_business_summary')->nullable()->after('logo_url');
            $table->string('website', 255)->nullable()->after('long_business_summary');
            $table->integer('full_time_employees')->nullable()->after('website');
        });
    }

    public function down(): void
    {
        Schema::table('asset_indicators', function (Blueprint $table) {
            $table->dropColumn(['long_business_summary', 'website', 'full_time_employees']);
        });
    }
};
