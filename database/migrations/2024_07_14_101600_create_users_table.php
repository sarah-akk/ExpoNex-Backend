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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 255);
            $table->string('email', 63)->unique();
            $table->string('username', 31)->unique();
            $table->string('channel_id', 15)->unique();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_pending')->default(false);
            $table->string('phone_number', 10)->nullable()->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
