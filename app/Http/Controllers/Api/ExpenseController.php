<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Services\ExpenseService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ExpenseController extends Controller
{
    protected ExpenseService $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    /**
     * Display a listing of the resource.
     * 
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $month = $request->query('month') ?? date('Y-m');

        // Cache the expenses list for 5 minutes
        $expenses = CacheService::cacheExpenses($user, $month, function () use ($user, $month) {
            return $this->expenseService->listForUserByMonth($user, $month);
        });

        return ExpenseResource::collection($expenses);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(ExpenseService::validationRules());

            $expense = $this->expenseService->create($request->user(), $validated);

            // Invalidate cache for the expense month
            $expenseMonth = date('Y-m', strtotime($validated['date']));
            CacheService::invalidateExpensesCache($request->user(), $expenseMonth);

            return response()->json([
                'message' => 'Expense created successfully',
                'data' => new ExpenseResource($expense->load('category'))
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * 
     * @param Expense $expense
     * @return JsonResponse
     */
    public function show(Expense $expense): JsonResponse
    {
        // Check ownership
        if ($expense->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'data' => new ExpenseResource($expense->load('category'))
        ]);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param Request $request
     * @param Expense $expense
     * @return JsonResponse
     */
    public function update(Request $request, Expense $expense): JsonResponse
    {
        try {
            $validated = $request->validate(ExpenseService::validationRules());

            $expense = $this->expenseService->update($request->user(), $expense, $validated);

            // Invalidate cache for the expense month
            $expenseMonth = date('Y-m', strtotime($validated['date']));
            CacheService::invalidateExpensesCache($request->user(), $expenseMonth);

            return response()->json([
                'message' => 'Expense updated successfully',
                'data' => new ExpenseResource($expense->load('category'))
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 403 ? 403 : 500;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param Request $request
     * @param Expense $expense
     * @return JsonResponse
     */
    public function destroy(Request $request, Expense $expense): JsonResponse
    {
        try {
            // Get the expense date before deletion
            $expenseMonth = date('Y-m', strtotime($expense->date));

            $this->expenseService->delete($request->user(), $expense);

            // Invalidate cache for the expense month
            CacheService::invalidateExpensesCache($request->user(), $expenseMonth);

            return response()->json([
                'message' => 'Expense deleted successfully'
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 403 ? 403 : 500;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
