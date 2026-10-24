<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class ProductVariantController extends Controller
{
    // GET /api/variants?product_id=1
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ProductVariant::with('product');

            if ($request->filled('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            return response()->json(['data' => $query->get()]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to fetch variants');
        }
    }

    // POST /api/variants
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id'     => 'required|integer|exists:products,product_id',
            'size_name'      => [
                'required', 'string', 'max:255',
                Rule::unique('product_variants')->where('product_id', $request->product_id),
            ],
            'price_modifier' => 'nullable|numeric',
        ]);

        try {
            $variant = ProductVariant::create($request->only('product_id', 'size_name', 'price_modifier'));

            return response()->json([
                'message' => 'Variant created',
                'data'    => $variant->fresh()->load('product'),
            ], 201);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to create variant');
        }
    }

    // GET /api/variants/{variant_id}
    public function show($variant_id): JsonResponse
    {
        try {
            $variant = ProductVariant::with('product')->find($variant_id);
            if (! $variant) {
                return response()->json(['error' => 'Variant not found'], 404);
            }

            return response()->json(['data' => $variant]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to fetch variant');
        }
    }

    // PUT/PATCH /api/variants/{variant_id}
    public function update(Request $request, $variant_id): JsonResponse
    {
        try {
            $variant = ProductVariant::find($variant_id);
            if (! $variant) {
                return response()->json(['error' => 'Variant not found'], 404);
            }

            $productId = $request->input('product_id', $variant->product_id);

            $request->validate([
                'product_id'     => 'sometimes|integer|exists:products,product_id',
                'size_name'      => [
                    'sometimes', 'string', 'max:255',
                    Rule::unique('product_variants')
                        ->where('product_id', $productId)
                        ->ignore($variant->variant_id, 'variant_id'),
                ],
                'price_modifier' => 'nullable|numeric',
            ]);

            $variant->update($request->only('product_id', 'size_name', 'price_modifier'));

            return response()->json(['message' => 'Variant updated', 'data' => $variant->load('product')]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to update variant');
        }
    }

    // DELETE /api/variants/{variant_id}
    public function destroy($variant_id): JsonResponse
    {
        try {
            $variant = ProductVariant::find($variant_id);
            if (! $variant) {
                return response()->json(['error' => 'Variant not found'], 404);
            }

            $variant->delete();

            return response()->json(['message' => 'Variant deleted']);
        } catch (QueryException $e) {
            // order_items uses restrictOnDelete on variant_id
            return response()->json([
                'error' => 'Cannot delete a variant that is used in orders',
            ], 409);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to delete variant');
        }
    }

    private function errorResponse(Throwable $e, string $message): JsonResponse
    {
        return response()->json([
            'error'   => $message,
            'message' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}
