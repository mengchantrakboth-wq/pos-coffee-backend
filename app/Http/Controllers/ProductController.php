<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductController extends Controller
{
    // GET /api/products?search=&category_id=&is_active=&per_page=
    public function index(Request $request): JsonResponse
    {
        try {
            $products = Product::with(['categories', 'variants'])
                ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
                ->when($request->category_id, fn($q, $id) => $q->where('category_id', $id))
                ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
                ->latest('product_id')
                ->paginate($request->integer('per_page', 15));

            return response()->json($products);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to fetch products');
        }
    }

    // POST /api/products
    public function store(Request $request): JsonResponse
    {
        $imagePath = null;

        try {
            $data = $request->validate([
                'name'        => 'required|string|max:255',
                'category_id' => 'required|exists:categories,category_id',
                'base_price'  => 'required|numeric|min:0',
                'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'is_active'   => 'sometimes|boolean',
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
                $data['image_path'] = $imagePath;
            }
            unset($data['image']);

            $product = Product::create($data);

            return response()->json([
                'message' => 'Product created successfully',
                'data'    => $product->load(['categories', 'variants']),
            ], 201);
        } catch (ValidationException $e) {
            throw $e; // keep Laravel's 422 response
        } catch (Throwable $e) {
            // remove the uploaded image if the DB insert failed
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            return $this->errorResponse($e, 'Failed to create product');
        }
    }

    // GET /api/products/{product}
    public function show(Product $product): JsonResponse
    {
        try {
            return response()->json([
                'data' => $product->load(['categories', 'variants', 'recipeItems']),
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to fetch product');
        }
    }

    // PUT/PATCH /api/products/{product}
    // For image uploads use POST + _method=PUT (multipart/form-data)
    public function update(Request $request, Product $product): JsonResponse
    {
        $newImagePath = null;

        try {
            $data = $request->validate([
                'name'        => 'sometimes|required|string|max:255',
                'category_id' => 'sometimes|required|exists:categories,category_id',
                'base_price'  => 'sometimes|required|numeric|min:0',
                'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'is_active'   => 'sometimes|boolean',
            ]);

            $oldImagePath = $product->image_path;

            if ($request->hasFile('image')) {
                $newImagePath = $request->file('image')->store('products', 'public');
                $data['image_path'] = $newImagePath;
            }
            unset($data['image']);

            $product->update($data);

            // delete the old image only after the update succeeded
            if ($newImagePath && $oldImagePath) {
                Storage::disk('public')->delete($oldImagePath);
            }

            return response()->json([
                'message' => 'Product updated successfully',
                'data'    => $product->fresh(['categories', 'variants']),
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }
            return $this->errorResponse($e, 'Failed to update product');
        }
    }

    // DELETE /api/products/{product}
    public function destroy(Product $product): JsonResponse
    {
        try {
            $imagePath = $product->image_path;

            $product->delete();

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json(['message' => 'Product deleted successfully']);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to delete product');
        }
    }

    /**
     * Log the error and return a consistent JSON 500 response.
     * The real exception message is only shown when APP_DEBUG=true.
     */
    private function errorResponse(Throwable $e, string $message): JsonResponse
    {
        Log::error($message, [
            'error' => $e->getMessage(),
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
        ]);

        return response()->json([
            'message' => $message,
            'error'   => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}
