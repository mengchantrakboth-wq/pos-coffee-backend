<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductController extends Controller
{
    // GET /api/products
    public function index(): JsonResponse
    {
        try {
            $products = Product::with(['categories', 'variants'])->get();

            return response()->json(['data' => $products]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to fetch products');
        }
    }

    // POST /api/products
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name'        => 'required|string|max:255',
                'category_id' => 'required|exists:categories,category_id',
                'base_price'  => 'required|numeric|min:0',
                'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'is_active'   => 'sometimes|boolean',
            ]);

            if ($request->hasFile('image')) {
                $data['image_path'] = $request->file('image')->store('products', 'public');
            }
            unset($data['image']);

            $product = Product::create($data);

            return response()->json([
                'message' => 'Product created successfully',
                'data'    => $product->load(['categories', 'variants']),
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to create product');
        }
    }

    // GET /api/products/{product}
    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $product->load(['categories', 'variants', 'recipeItems']),
        ]);
    }

    // PUT /api/products/{product}
    // For image upload send POST + _method=PUT as form-data
    public function update(Request $request, Product $product): JsonResponse
    {
        try {
            $data = $request->validate([
                'name'        => 'sometimes|required|string|max:255',
                'category_id' => 'sometimes|required|exists:categories,category_id',
                'base_price'  => 'sometimes|required|numeric|min:0',
                'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'is_active'   => 'sometimes|boolean',
            ]);

            if ($request->hasFile('image')) {
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $data['image_path'] = $request->file('image')->store('products', 'public');
            }
            unset($data['image']);

            $product->update($data);

            return response()->json([
                'message' => 'Product updated successfully',
                'data'    => $product->load(['categories', 'variants']),
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to update product');
        }
    }

    // DELETE /api/products/{product}
    public function destroy(Product $product): JsonResponse
    {
        try {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $product->delete();

            return response()->json(['message' => 'Product deleted successfully']);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to delete product');
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
