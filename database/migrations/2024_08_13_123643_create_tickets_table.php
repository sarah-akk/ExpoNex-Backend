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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_id')->constrained()->cascadeOnDelete();
            $table->string('title', 63);
            $table->string('description');
            $table->string('barcode', 10);
            $table->integer('in_place', false, true);
            $table->integer('available_in_place', false, true);
            $table->integer('in_place_price', false, true);
            $table->integer('in_virtual_price', false, true);
            $table->integer('prime', false, true);
            $table->integer('available_prime', false, true);
            $table->integer('prime_price', false, true);
            $table->string('side_style', 15)->nullable();
            $table->string('main_style', 15)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
