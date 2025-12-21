<?php

namespace App\Services;

use App\Models\Income;
use App\Models\User;
use Illuminate\Support\Collection;

class IncomeService
{
    /**
     * Lấy danh sách thu nhập theo tháng của User.
     */
    public function listForUserByMonth(User $user, string $month): Collection
    {
        [$year, $monthNum] = explode('-', $month);

        return $user->incomes()
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Tạo mới thu nhập.
     */
    public function create(User $user, array $data): Income
    {
        return $user->incomes()->create($data);
    }

    /**
     * Cập nhật thu nhập (Có kiểm tra quyền sở hữu).
     */
    public function update(User $user, Income $income, array $data): Income
    {
        if ($income->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $income->update($data);
        return $income;
    }

    /**
     * Xóa thu nhập.
     */
    public function delete(User $user, Income $income): bool
    {
        if ($income->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        return $income->delete();
    }

    /**
     * Validation rules for creating/updating income
     */
    public static function validationRules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:255',
            'date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ];
    }
}
