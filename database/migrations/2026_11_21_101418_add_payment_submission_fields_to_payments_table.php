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
        Schema::table('payments', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('payment_date');
            $table->string('payment_method')->nullable()->after('due_date');
            $table->string('receipt_path')->nullable()->after('payment_method');
            $table->timestamp('submitted_at')->nullable()->after('receipt_path');
            $table->timestamp('paid_at')->nullable()->after('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'due_date',
                'payment_method',
                'receipt_path',
                'submitted_at',
                'paid_at',
            ]);
        });
    }
};
