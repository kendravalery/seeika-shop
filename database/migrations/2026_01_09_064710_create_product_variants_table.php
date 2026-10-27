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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // unique SKU for each variant
            $table->string('sku')->unique()->nullable();

            $table->decimal('price', 15, 2);

            // Original price before discount
            $table->decimal('compare_price', 15, 2)->nullable();

            $table->unsignedInteger('stock')->default(0);

            // Maximum quantity allowed in one order
            // Null means no additional per-order limit
            $table->unsignedInteger('max_purchase_per_order')->nullable();

            // Maximum total quantity accepted for preorder
            // Null means no total preorder limit
            $table->unsignedInteger('preorder_limit')->nullable();

            $table->decimal('weight', 10, 2)->default(0);

            $table->boolean('is_active')->default(true);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
