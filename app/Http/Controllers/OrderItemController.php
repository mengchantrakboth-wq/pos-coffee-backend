<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    /**
     * Get items in an order
     */
    public function index($order_id)
    {
        try {
            $order = Order::find($order_id);
            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            $items = OrderItem::where('order_id', $order_id)->get();
            return response()->json($items);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch items',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add item to order
     */
    public function store(Request $request, $order_id)
    {
        try {
            $order = Order::find($order_id);
            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            $request->validate([
                'product_id' => 'required|integer|exists:products,product_id',
                'quantity' => 'required|integer|min:1',
                'unit_price' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            $item = OrderItem::create([
                'order_id' => $order_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'subtotal' => $request->quantity * $request->unit_price,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'message' => 'Item added to order',
                'item' => $item,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to add item',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update item
     */
    public function update(Request $request, $order_id, $item_id)
    {
        try {
            $item = OrderItem::where('order_id', $order_id)
                ->where('order_item_id', $item_id)
                ->first();

            if (!$item) {
                return response()->json(['error' => 'Item not found'], 404);
            }

            $request->validate([
                'quantity' => 'nullable|integer|min:1',
                'unit_price' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            $quantity = $request->quantity ?? $item->quantity;
            $unit_price = $request->unit_price ?? $item->unit_price;

            $item->update([
                'quantity' => $quantity,
                'unit_price' => $unit_price,
                'subtotal' => $quantity * $unit_price,
                'notes' => $request->notes ?? $item->notes,
            ]);

            return response()->json([
                'message' => 'Item updated',
                'item' => $item,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update item',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove item
     */
    public function destroy($order_id, $item_id)
    {
        try {
            $item = OrderItem::where('order_id', $order_id)
                ->where('order_item_id', $item_id)
                ->first();

            if (!$item) {
                return response()->json(['error' => 'Item not found'], 404);
            }

            $item->delete();

            return response()->json(['message' => 'Item removed']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete item',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
