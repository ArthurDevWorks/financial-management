<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('limit', 12, 2)->default(0);
            $table->tinyInteger('closing_day')->unsigned(); // 1–28: dia de fechamento da fatura
            $table->tinyInteger('due_day')->unsigned();     // 1–28: dia de vencimento da fatura
            $table->string('color', 7)->default('#22c9a2'); // cor hex do cartão
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_cards');
    }
};
