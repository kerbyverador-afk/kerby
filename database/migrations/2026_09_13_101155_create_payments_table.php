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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');

            // Ibutang dinhi ang bag-ong mga column:
            $table->string('tenant_name')->nullable();

            $table->decimal('amount', 8, 2);
            $table->date('payment_date')->nullable();

            $table->string('month_year'); // Kinahanglan kini aron mawala ang error sa Postman

            $table->string('status');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
