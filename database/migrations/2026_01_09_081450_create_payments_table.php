<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Amount requested for this payment attempt
            $table->decimal('amount', 15, 2);

            // gateway anyting
            $table->string('gateway');

            // bank transfer ,E-wallet, qris,credit card dll
            $table->string('payment_method')->nullable();

            // bca_va,gopay, qris dll
            $table->string('payment_type')->nullable();

            // ID transaksi dari gateway
            $table->string('transaction_id')->nullable();

            // pending , settlement, expire, refund, dll.
            $table->string('transaction_status')->nullable();

            // untuk midtrans accpet ,denly,challenge maksudnya transaksi di anggap mencurigakan
            $table->string('fraud_status')->nullable();

            // di pakai untuk midtrans snap ,kalo gak di pake bakal null aja
            $table->string('snap_token')->nullable();

            // menyimpan response lengkap dari gateway
            $table->json('response')->nullable();

            // Payment expiration deadline
            $table->timestamp('expires_at')->nullable();

            // waktu pembayaran berhasl
            $table->timestamp('paid_at')->nullable();

            // Prevent duplicate gateway transaction IDs
            $table->unique(['gateway', 'transaction_id'], 'payments_gateway_transaction_unique');

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
