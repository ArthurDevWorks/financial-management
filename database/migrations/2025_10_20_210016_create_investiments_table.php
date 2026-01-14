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
        Schema::create('investiments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type')->constrained('categories');
            $table->string('name');
            $table->dateTime('dt_investment');
            $table->decimal('value', 15, 2);
            $table->integer('profitability');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investiments');
    }
};
