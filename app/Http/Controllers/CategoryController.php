<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class CategoryController extends Controller
{
    // GET /api/categories
    public function index(): JsonResponse
    {
        try {
            $categories = Categories::all();

            return response()->json(['data' => $categories]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to fetch categories');
        }
    }

    // POST /api/categories
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
            ]);

            $category = Categories::create([
                'name' => $request->name,
            ]);

            return response()->json([
                'message' => 'Category created successfully',
                'data'    => $category,
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to create category');
        }
    }

    // GET /api/categories/{category}
    public function show(Categories $category): JsonResponse
    {
        return response()->json(['data' => $category]);
    }

    // PUT/PATCH /api/categories/{category}
    public function update(Request $request, Categories $category): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $category->category_id . ',category_id',
            ]);

            $category->update([
                'name' => $request->name,
            ]);

            return response()->json([
                'message' => 'Category updated successfully',
                'data'    => $category,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to update category');
        }
    }

    // DELETE /api/categories/{category}
    public function destroy(Categories $category): JsonResponse
    {
        try {
            if ($category->products()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete: this category still has products',
                ], 409);
            }

            $category->delete();

            return response()->json(['message' => 'Category deleted successfully']);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to delete category');
        }
    }

    private function errorResponse(Throwable $e, string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'error'   => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}
