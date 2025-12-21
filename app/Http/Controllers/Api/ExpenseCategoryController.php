<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExpenseCategoryResource;
use App\Models\ExpenseCategory;
use App\Services\ExpenseCategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ExpenseCategoryController extends Controller
{
    protected ExpenseCategoryService $categoryService;

    public function __construct(ExpenseCategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $categories = $this->categoryService->listForUser($user);

        return ExpenseCategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(
                ExpenseCategoryService::createValidationRules($request->user()->id)
            );

            $category = $this->categoryService->create($request->user(), $validated);

            return response()->json([
                'message' => 'Category created successfully',
                'data' => new ExpenseCategoryResource($category)
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ExpenseCategory $expenseCategory): JsonResponse
    {
        if ($expenseCategory->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'data' => new ExpenseCategoryResource($expenseCategory)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        try {
            $validated = $request->validate(
                ExpenseCategoryService::updateValidationRules($request->user()->id, $expenseCategory->id)
            );

            $category = $this->categoryService->update($request->user(), $expenseCategory, $validated);

            return response()->json([
                'message' => 'Category updated successfully',
                'data' => new ExpenseCategoryResource($category)
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
     */
    public function destroy(Request $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        try {
            $this->categoryService->delete($request->user(), $expenseCategory);

            return response()->json([
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 403 ? 403 : 422;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
