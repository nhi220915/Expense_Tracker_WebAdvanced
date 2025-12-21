<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Support\Collection;

class BudgetService
{
    /**
     * Lấy danh sách ngân sách theo tháng của User.
     */
    public function listForUserByMonth(User $user, int $year, int $month): Collection
    {
        return Budget::where('user_id', $user->id)
            ->where('year', $year)
            ->where('month', $month)
            ->with('category')
            ->get();
    }

    /**
     * Tạo hoặc cập nhật ngân sách.
     */
    public function createOrUpdate(User $user, array $data): Budget
    {
        // Validate that the expense category belongs to the user
        $category = ExpenseCategory::find($data['expense_category_id']);
        if (!$category || $category->user_id !== $user->id) {
            abort(403, 'Invalid expense category.');
        }

        // Check if budget already exists
        $existingBudget = Budget::where('user_id', $user->id)
            ->where('expense_category_id', $data['expense_category_id'])
            ->where('year', $data['year'])
            ->where('month', $data['month'])
            ->first();

        if ($existingBudget) {
            $existingBudget->update(['limit' => $data['limit']]);
            return $existingBudget;
        }

        return Budget::create([
            'user_id' => $user->id,
            'expense_category_id' => $data['expense_category_id'],
            'limit' => $data['limit'],
            'month' => $data['month'],
            'year' => $data['year'],
        ]);
    }

    /**
     * Cập nhật tổng giới hạn ngân sách (overall limit).
     */
    public function updateOverallLimit(User $user, float $overallLimit, int $year, int $month): bool
    {
        $budgets = $this->listForUserByMonth($user, $year, $month);

        if ($budgets->isEmpty()) {
            // Create budgets for all categories with equal distribution
            $categories = ExpenseCategory::where('user_id', $user->id)->get();

            if ($categories->isEmpty()) {
                throw new \Exception('No expense categories found. Please create categories first.');
            }

            $limitPerCategory = round($overallLimit / $categories->count(), 2);
            $totalDistributed = 0;

            foreach ($categories as $index => $category) {
                if ($index === $categories->count() - 1) {
                    $limit = round($overallLimit - $totalDistributed, 2);
                } else {
                    $limit = $limitPerCategory;
                    $totalDistributed += $limit;
                }

                Budget::create([
                    'user_id' => $user->id,
                    'expense_category_id' => $category->id,
                    'limit' => $limit,
                    'month' => $month,
                    'year' => $year,
                ]);
            }
        } else {
            // Update existing budgets proportionally
            $currentTotal = $budgets->sum('limit');
            $ratio = $currentTotal > 0 ? $overallLimit / $currentTotal : 1;

            foreach ($budgets as $budget) {
                $budget->update(['limit' => round($budget->limit * $ratio, 2)]);
            }
        }

        return true;
    }

    /**
     * Cập nhật phân bổ ngân sách theo phần trăm.
     */
    public function updateAllocation(User $user, array $percentages, int $year, int $month): bool
    {
        // Validate total is 100%
        $total = array_sum($percentages);
        if ($total != 100) {
            throw new \Exception('Total allocation must equal 100%');
        }

        // Get overall limit from existing budgets
        $existingBudgets = $this->listForUserByMonth($user, $year, $month);
        $overallLimit = $existingBudgets->sum('limit');

        if ($overallLimit == 0) {
            $overallLimit = 2000; // Default
        }

        // Get expense categories
        $categories = ExpenseCategory::where('user_id', $user->id)
            ->whereIn('name', array_keys($percentages))
            ->get();

        $allocatedCategoryIds = $categories->pluck('id')->toArray();

        // Update or create budgets
        foreach ($categories as $category) {
            $percentage = $percentages[$category->name] ?? 0;
            $limit = ($percentage / 100) * $overallLimit;

            Budget::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'expense_category_id' => $category->id,
                    'year' => $year,
                    'month' => $month,
                ],
                ['limit' => round($limit, 2)]
            );
        }

        // Delete budgets not in allocation
        Budget::where('user_id', $user->id)
            ->where('year', $year)
            ->where('month', $month)
            ->whereNotIn('expense_category_id', $allocatedCategoryIds)
            ->delete();

        return true;
    }

    /**
     * Validation rules for creating/updating budget
     */
    public static function validationRules(): array
    {
        return [
            'expense_category_id' => 'required|exists:expense_categories,id',
            'limit' => 'required|numeric|min:1',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ];
    }

    /**
     * Validation rules for updating overall limit
     */
    public static function updateLimitValidationRules(): array
    {
        return [
            'overall_limit' => 'required|numeric|min:100',
            'month' => 'required|string',
        ];
    }

    /**
     * Validation rules for updating allocation
     */
    public static function updateAllocationValidationRules(): array
    {
        return [
            'percentages' => 'required|array',
            'month' => 'required|string',
        ];
    }
}
