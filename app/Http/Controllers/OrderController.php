<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Get all orders
     */
    public function index(Request $request)
    {
        try {
            $orders = Order::with(['customer', 'user', 'table', 'orderItems'])
                ->orderBy('order_id', 'desc')
                ->paginate(15);

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch orders',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single order
     */
    public function show($order_id)
    {
        try {
            $order = Order::with(['customer', 'user', 'table', 'orderItems'])
                ->find($order_id);

            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new order
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'customer_id' => 'nullable|integer|exists:customers,customer_id',
                'table_id' => 'nullable|integer|exists:tables,table_id',
                'order_type' => 'required|string|in:dine-in,takeaway,delivery',
                'total_amount' => 'required|numeric|min:0',
            ]);

            $order = Order::create([
                'customer_id' => $request->customer_id ?? 1,  // ← Default to walk-in
                'user_id' => auth('api')->id() ?? 1,  // ← Default to system user
                'table_id' => $request->table_id,
                'order_date' => now(),
                'order_type' => $request->order_type,
                'order_status' => 'pending',
                'total_amount' => $request->total_amount,
            ]);

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update order status
     */
    public function update(Request $request, $order_id)
    {
        try {
            $order = Order::find($order_id);

            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            $request->validate([
                'order_status' => 'required|string|in:pending,preparing,ready,completed,cancelled',
                'total_amount' => 'nullable|numeric|min:0',
            ]);

            $order->update([
                'order_status' => $request->order_status,
                'total_amount' => $request->total_amount ?? $order->total_amount,
            ]);

            return response()->json([
                'message' => 'Order updated successfully',
                'order' => $order,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete order
     */
    public function destroy($order_id)
    {
        try {
            $order = Order::find($order_id);

            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            $order->delete();

            return response()->json(['message' => 'Order deleted']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders by status
     */
    public function getByStatus($status)
    {
        try {
            $orders = Order::where('order_status', $status)
                ->with(['customer', 'user', 'orderItems'])
                ->orderBy('order_id', 'desc')
                ->get();

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch orders',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's orders
     */
    public function getTodayOrders()
    {
        try {
            $today = now()->startOfDay();
            $tomorrow = now()->addDay()->startOfDay();

            $orders = Order::whereBetween('order_date', [$today, $tomorrow])
                ->with(['customer', 'user', 'orderItems'])
                ->orderBy('order_id', 'desc')
                ->get();

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch today orders',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
