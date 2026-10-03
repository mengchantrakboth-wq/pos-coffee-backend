<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderItemController extends Controller
{
    // GET /api/orders/{order_id}/items
    public function index($order_id): JsonResponse
    {
        try {
            $order = Order::find($order_id);
            if (! $order) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            $items = OrderItem::with('variant')->where('order_id', $order_id)->get();

            return response()->json(['data' => $items]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to fetch items');
        }
    }

    // POST /api/orders/{order_id}/items
    public function store(Request $request, $order_id): JsonResponse
    {
        try {
            $order = Order::find($order_id);
            if (! $order) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            $request->validate([
                'variant_id' => 'required|integer|exists:product_variants,variant_id',
                'quantity'   => 'required|integer|min:1',
                'unit_price' => 'required|numeric|min:0',
            ]);

            $item = OrderItem::create([
                'order_id'   => $order_id,
                'variant_id' => $request->variant_id,
                'quantity'   => $request->quantity,
                'unit_price' => $request->unit_price,
                'subtotal'   => $request->quantity * $request->unit_price,
            ]);

            $this->updateOrderTotal($order);

            return response()->json([
                'message' => 'Item added to order',
                'data'    => $item->load('variant'),
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to add item');
        }
    }

    // PUT/PATCH /api/orders/{order_id}/items/{item_id}
    public function update(Request $request, $order_id, $item_id): JsonResponse
    {
        try {
            $item = OrderItem::where('order_id', $order_id)
                ->where('order_item_id', $item_id)
                ->first();

            if (! $item) {
                return response()->json(['error' => 'Item not found'], 404);
            }

            $request->validate([
                'quantity'   => 'nullable|integer|min:1',
                'unit_price' => 'nullable|numeric|min:0',
            ]);

            $quantity   = $request->quantity ?? $item->quantity;
            $unit_price = $request->unit_price ?? $item->unit_price;

            $item->update([
                'quantity'   => $quantity,
                'unit_price' => $unit_price,
                'subtotal'   => $quantity * $unit_price,
            ]);

            $this->updateOrderTotal(Order::find($order_id));

            return response()->json(['message' => 'Item updated', 'data' => $item]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to update item');
        }
    }

    // DELETE /api/orders/{order_id}/items/{item_id}
    public function destroy($order_id, $item_id): JsonResponse
    {
        try {
            $item = OrderItem::where('order_id', $order_id)
                ->where('order_item_id', $item_id)
                ->first();

            if (! $item) {
                return response()->json(['error' => 'Item not found'], 404);
            }

            $item->delete();

            $this->updateOrderTotal(Order::find($order_id));

            return response()->json(['message' => 'Item removed']);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to delete item');
        }
    }

    // keeps orders.total_amount equal to the sum of its items
    private function updateOrderTotal(Order $order): void
    {
        $order->update([
            'total_amount' => OrderItem::where('order_id', $order->order_id)->sum('subtotal'),
        ]);
    }

    private function errorResponse(Throwable $e, string $message): JsonResponse
    {
        return response()->json([
            'error'   => $message,
            'message' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}
