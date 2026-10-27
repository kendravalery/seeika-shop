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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            //label alamat
            $table->string('label')->nullable();
            // contoh rumah kantor

            $table->string('receiver_name');
            $table->string('phone', 20);

            // lokasi
            $table->string('province');
            $table->string('city');
            $table->string('district');
            $table->string('village')->nullable();
            $table->string('postal_code');

            // detail lengkap
            $table->text('full_address');

            // alamat utama
            $table->boolean('is_default')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
