<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;
use App\Services\BudgetService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BudgetController extends Controller
{
    protected BudgetService $budgetService;

    public function __construct(BudgetService $budgetService)
    {
        $this->budgetService = $budgetService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $month = $request->query('month', date('Y-m'));
        [$year, $monthNum] = explode('-', $month);

        // Cache the budgets list for 1 hour
        $budgets = CacheService::cacheBudgets($user, (int) $year, (int) $monthNum, function () use ($user, $year, $monthNum) {
            return $this->budgetService->listForUserByMonth($user, (int) $year, (int) $monthNum);
        });

        return BudgetResource::collection($budgets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(BudgetService::validationRules());

            $budget = $this->budgetService->createOrUpdate($request->user(), $validated);

            // Invalidate cache for the budget month
            [$year, $month] = explode('-', $validated['month']);
            CacheService::invalidateBudgetsCache($request->user(), (int) $year, (int) $month);

            return response()->json([
                'message' => 'Budget created/updated successfully',
                'data' => new BudgetResource($budget->load('category'))
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 403 ? 403 : 500;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Budget $budget): JsonResponse
    {
        if ($budget->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'data' => new BudgetResource($budget->load('category'))
        ]);
    }

    /**
     * Update overall budget limit.
     */
    public function updateLimit(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(BudgetService::updateLimitValidationRules());

            [$year, $month] = explode('-', $validated['month']);

            $this->budgetService->updateOverallLimit(
                $request->user(),
                $validated['overall_limit'],
                (int) $year,
                (int) $month
            );

            // Invalidate cache for the budget month
            CacheService::invalidateBudgetsCache($request->user(), (int) $year, (int) $month);

            return response()->json([
                'message' => 'Budget limit updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update budget allocation by percentages.
     */
    public function updateAllocation(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(BudgetService::updateAllocationValidationRules());

            [$year, $month] = explode('-', $validated['month']);

            $this->budgetService->updateAllocation(
                $request->user(),
                $validated['percentages'],
                (int) $year,
                (int) $month
            );

            // Invalidate cache for the budget month
            CacheService::invalidateBudgetsCache($request->user(), (int) $year, (int) $month);

            return response()->json([
                'message' => 'Budget allocation updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Budget $budget): JsonResponse
    {
        try {
            // Check ownership
            if ($budget->user_id !== $request->user()->id) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'limit' => 'required|numeric|min:1',
            ]);

            $budget->update($validated);

            return response()->json([
                'message' => 'Budget updated successfully',
                'data' => new BudgetResource($budget->load('category'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Budget $budget): JsonResponse
    {
        try {
            if ($budget->user_id !== $request->user()->id) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $budget->delete();

            return response()->json([
                'message' => 'Budget deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
