<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Collection;

class ExpenseService
{
    /**
     * Lấy danh sách chi phí theo tháng của User.
     */
    public function listForUserByMonth(User $user, string $month): Collection
    {
        [$year, $monthNum] = explode('-', $month);

        return $user->expenses()
            ->with('category') // Tránh lỗi N+1
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Tạo mới chi phí.
     */
    public function create(User $user, array $data): Expense
    {
        return $user->expenses()->create($data);
    }

    /**
     * Cập nhật chi phí (Có kiểm tra quyền sở hữu).
     */
    public function update(User $user, Expense $expense, array $data): Expense
    {
        if ($expense->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $expense->update($data);
        return $expense;
    }

    /**
     * Xóa chi phí.
     */
    public function delete(User $user, Expense $expense): bool
    {
        if ($expense->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        return $expense->delete();
    }

    /**
     * Validation rules for creating/updating expense
     */
    public static function validationRules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ];
    }
}