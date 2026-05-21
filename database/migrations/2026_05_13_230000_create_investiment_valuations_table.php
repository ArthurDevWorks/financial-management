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
        Schema::create('investiment_valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investiment_id')->constrained('investiments')->cascadeOnDelete();
            $table->json('assumptions');
            $table->json('projected_cash_flows');
            $table->json('summary');
            $table->timestamp('calculated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investiment_valuations');
    }
};
