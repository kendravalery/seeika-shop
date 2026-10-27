<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('quantity')->default(1);

            $table->boolean('is_selected')->default(false);

            // biar 1 user tidak dobel variant  yang sama
            $table->unique(['user_id', 'product_variant_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
