<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IncomeResource;
use App\Models\Income;
use App\Services\IncomeService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class IncomeController extends Controller
{
    protected IncomeService $incomeService;

    public function __construct(IncomeService $incomeService)
    {
        $this->incomeService = $incomeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $month = $request->query('month', date('Y-m'));

        // Cache the incomes list for 5 minutes
        $incomes = CacheService::cacheIncomes($user, $month, function () use ($user, $month) {
            return $this->incomeService->listForUserByMonth($user, $month);
        });

        return IncomeResource::collection($incomes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(IncomeService::validationRules());

            $income = $this->incomeService->create($request->user(), $validated);

            // Invalidate cache for the income month
            $incomeMonth = date('Y-m', strtotime($validated['date']));
            CacheService::invalidateIncomesCache($request->user(), $incomeMonth);

            return response()->json([
                'message' => 'Income created successfully',
                'data' => new IncomeResource($income)
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create income',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Income $income): JsonResponse
    {
        if ($income->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'data' => new IncomeResource($income)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Income $income): JsonResponse
    {
        if ($income->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $validated = $request->validate(IncomeService::validationRules());

            $income = $this->incomeService->update($request->user(), $income, $validated);

            // Invalidate cache for the income month
            $incomeMonth = date('Y-m', strtotime($validated['date']));
            CacheService::invalidateIncomesCache($request->user(), $incomeMonth);

            return response()->json([
                'message' => 'Income updated successfully',
                'data' => new IncomeResource($income)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Income $income): JsonResponse
    {
        if ($income->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            // Get the income date before deletion
            $incomeMonth = date('Y-m', strtotime($income->date));

            $this->incomeService->delete($request->user(), $income);

            // Invalidate cache for the income month
            CacheService::invalidateIncomesCache($request->user(), $incomeMonth);

            return response()->json([
                'message' => 'Income deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
