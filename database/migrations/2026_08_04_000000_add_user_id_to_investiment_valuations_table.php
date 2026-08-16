<?php

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
        Schema::table('investiment_valuations', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('asset_id')
                ->constrained('users')
                ->cascadeOnDelete();
        });

        // Backfill: vincula registros órfãos ao primeiro usuário existente
        $userId = DB::table('users')->orderBy('id')->value('id');
        if ($userId !== null) {
            DB::table('investiment_valuations')
                ->whereNull('user_id')
                ->update(['user_id' => $userId]);
        }

        Schema::table('investiment_valuations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('investiment_valuations', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investiment_valuations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
