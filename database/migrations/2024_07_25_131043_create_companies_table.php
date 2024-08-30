<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 127);
            $table->string('companyname', 127);
            $table->text('description');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_pending')->default(false);
            $table->boolean('is_approval')->nullable()->default(null);
            $table->boolean('show_owner')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
