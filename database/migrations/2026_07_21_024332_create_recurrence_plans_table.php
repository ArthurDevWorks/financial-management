<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurrence_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['revenue', 'expense']);
            $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'pix'])->nullable();
            $table->enum('frequency', ['monthly', 'yearly', 'weekly', 'biweekly', 'quarterly']);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('next_generation');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('releases', function (Blueprint $table) {
            $table->foreignId('recurrence_id')->nullable()->constrained('recurrence_plans')->nullOnDelete()->after('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('releases', function (Blueprint $table) {
            $table->dropForeign(['recurrence_id']);
            $table->dropColumn('recurrence_id');
        });

        Schema::dropIfExists('recurrence_plans');
    }
};
