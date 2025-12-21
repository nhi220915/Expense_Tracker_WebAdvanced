<?php

namespace App\Services;

use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Support\Collection;

class ExpenseCategoryService
{
    /**
     * Lấy danh sách expense categories của User.
     */
    public function listForUser(User $user): Collection
    {
        return ExpenseCategory::where('user_id', $user->id)
            ->orderBy('name')
            ->get();
    }

    /**
     * Tạo mới expense category.
     */
    public function create(User $user, array $data): ExpenseCategory
    {
        return ExpenseCategory::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'color' => $data['color'] ?? '#00a896',
        ]);
    }

    /**
     * Cập nhật expense category (Có kiểm tra quyền sở hữu).
     */
    public function update(User $user, ExpenseCategory $category, array $data): ExpenseCategory
    {
        if ($category->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $category->update($data);
        return $category;
    }

    /**
     * Xóa expense category.
     */
    public function delete(User $user, ExpenseCategory $category): bool
    {
        if ($category->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if category has expenses
        if ($category->expenses()->count() > 0) {
            throw new \Exception('Cannot delete category with existing expenses!');
        }

        return $category->delete();
    }

    /**
     * Tạo default categories cho user mới.
     */
    public function createDefaultCategories(User $user): void
    {
        $defaultCategories = [
            ['name' => 'Food', 'color' => '#00d1c1'],
            ['name' => 'Transport', 'color' => '#27ae60'],
            ['name' => 'Entertainment', 'color' => '#f39c12'],
            ['name' => 'Utilities', 'color' => '#e74c3c'],
            ['name' => 'Other', 'color' => '#00a896'],
        ];

        foreach ($defaultCategories as $category) {
            ExpenseCategory::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $category['name'],
                ],
                [
                    'color' => $category['color'],
                ]
            );
        }
    }

    /**
     * Validation rules for creating category
     */
    public static function createValidationRules(int $userId): array
    {
        return [
            'name' => 'required|string|max:255|unique:expense_categories,name,NULL,id,user_id,' . $userId,
            'color' => 'nullable|string|max:7',
        ];
    }

    /**
     * Validation rules for updating category
     */
    public static function updateValidationRules(int $userId, int $categoryId): array
    {
        return [
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $categoryId . ',id,user_id,' . $userId,
            'color' => 'nullable|string|max:7',
        ];
    }
}
