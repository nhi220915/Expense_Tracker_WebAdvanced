<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category'); // Food, Transport, ...
            $table->decimal('limit', 12, 2);
            $table->unsignedTinyInteger('month'); // 1-12
            $table->unsignedSmallInteger('year');
            $table->timestamps();

            $table->unique(['user_id', 'category', 'year', 'month']);
            $table->index(['user_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};