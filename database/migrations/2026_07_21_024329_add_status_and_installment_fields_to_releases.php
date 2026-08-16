<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('releases', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'pix'])->nullable()->after('type');
            $table->enum('status', ['pending', 'paid', 'canceled'])->default('paid')->after('payment_method');
            $table->tinyInteger('installment_number')->unsigned()->nullable()->after('status');
            $table->tinyInteger('total_installments')->unsigned()->nullable()->after('installment_number');
            $table->foreignId('parent_id')->nullable()->constrained('releases')->nullOnDelete()->after('total_installments');
        });
    }

    public function down(): void
    {
        Schema::table('releases', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['payment_method', 'status', 'installment_number', 'total_installments', 'parent_id']);
        });
    }
};
