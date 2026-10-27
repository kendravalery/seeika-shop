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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();

            $table->string('product_name');

            $table->string('sku')->nullable();

            // variant information snapshot
            $table->string('option_name')->nullable();
            $table->string('variant_name')->nullable();

            // supports multiple options, e.g. Color and Size
            $table->json('variant_details')->nullable();

            // how the item was sold at the time of purchase
            $table->enum('fulfillment_type', ['ready', 'preorder'])->default('ready');

            // unit price charged at the time of purchase
            $table->decimal('price', 15, 2);
            
            // Number of units purchased in this order item
            $table->unsignedInteger('quantity');

            // unit price multiplied by quantity, before order-level discounts
            $table->decimal('subtotal', 15, 2);

            $table->unsignedInteger('refunded_quantity')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
