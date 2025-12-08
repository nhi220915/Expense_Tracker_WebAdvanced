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

            // User
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // New: liên kết đến bảng expense_categories
            $table->foreignId('expense_category_id')
                ->constrained('expense_categories')
                ->cascadeOnDelete();

            $table->decimal('limit', 12, 2);

            $table->unsignedTinyInteger('month'); // 1-12
            $table->unsignedSmallInteger('year');

            $table->timestamps();

            // Unique theo user + category + thời gian
            $table->unique(['user_id', 'expense_category_id', 'year', 'month']);

            // Index phụ
            $table->index(['user_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
