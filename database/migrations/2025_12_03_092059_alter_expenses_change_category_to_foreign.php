<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Drop cột category cũ
            $table->dropColumn('category');

            // Thêm cột expense_category_id
            $table->foreignId('expense_category_id')
                  ->constrained('expense_categories')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Rollback: xóa foreign key
            $table->dropForeign(['expense_category_id']);
            $table->dropColumn('expense_category_id');

            // Thêm lại cột category cũ
            $table->string('category');
        });
    }
};
