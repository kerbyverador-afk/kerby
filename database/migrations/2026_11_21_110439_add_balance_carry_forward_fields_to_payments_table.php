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
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('is_billing_record')->default(false)->after('user_id');
            $table->decimal('carried_in_amount', 10, 2)->default(0)->after('amount_paid');
            $table->decimal('carried_balance', 10, 2)->default(0)->after('carried_in_amount');
            $table->foreignId('carried_to_payment_id')
                ->nullable()
                ->after('carried_balance')
                ->constrained('payments')
                ->nullOnDelete();
        });

        DB::table('payments')
            ->whereNull('submitted_at')
            ->update(['is_billing_record' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['carried_to_payment_id']);
            $table->dropColumn([
                'is_billing_record',
                'carried_in_amount',
                'carried_balance',
                'carried_to_payment_id',
            ]);
        });
    }
};
