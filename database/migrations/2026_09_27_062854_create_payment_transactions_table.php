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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64)->unique(); // TRX-YYYYMMDD-XXXXXX
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();

            $table->string('type', 30)->default('deposit'); // deposit, order_payment
            $table->decimal('amount', 12, 2);
            $table->decimal('fee', 12, 2)->default(0.00);
            $table->string('currency', 10)->default('BDT');
            $table->string('gateway_name', 50)->default('uddoktapay');

            $table->string('invoice_id', 100)->nullable()->index(); // Gateway invoice identifier
            $table->string('gateway_transaction_id', 100)->nullable()->index(); // Sender TrxID
            $table->string('payment_channel', 50)->nullable(); // bkash, nagad, rocket

            $table->string('status', 30)->default('pending')->index(); // pending, completed, failed, cancelled, refunded
            $table->text('payment_url')->nullable();

            $table->json('metadata')->nullable();
            $table->json('raw_payload')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
