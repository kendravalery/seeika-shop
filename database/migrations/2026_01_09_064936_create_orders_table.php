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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();

            // supaya data address tidak hilang ,ketimbang menggunakan relasi data address bisa hilang saat user menghapus lokasi nya ,di akunya
            $table->string('receiver_name');
            $table->string('phone');
            $table->string('province');
            $table->string('city');
            $table->string('district');
            $table->string('village')->nullable();
            $table->string('postal_code');
            $table->text('full_address');
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('status', ['pending', 'paid', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'])->default('pending');
            // To see who canceled the order.
            $table->enum('cancelled_by', ['customer', 'admin'])->nullable();

            // Actually, what’s the reason for the cancellation?
            $table->text('cancel_reason')->nullable();

            $table->string('order_number')->unique();

            $table->decimal('subtotal', 15, 2);
            // Total discount applied to the order
            $table->decimal('discount_total', 15, 2)->default(0);

            // Snapshot of the voucher code used, if any
            $table->string('voucher_code')->nullable();

            $table->decimal('shipping_cost', 15, 2)->default(0);

            $table->decimal('total', 15, 2);

            $table->enum('payment_status', ['unpaid', 'paid', 'failed', 'refunded'])->default('unpaid');
            $table->string('payment_method')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
