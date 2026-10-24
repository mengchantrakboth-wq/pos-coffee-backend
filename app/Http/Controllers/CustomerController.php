<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class CustomerController extends Controller
{
    // GET /api/customers?search=012
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Customer::query();

            // Search by name or phone (handy at the POS counter)
            if ($request->filled('search')) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            return response()->json(['data' => $query->orderBy('customer_id')->get()]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to fetch customers');
        }
    }

    // POST /api/customers
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name'  => 'required|string|max:255',
                'phone' => 'required|string|max:20|unique:customers,phone',
            ]);

            // loyalty_points starts at 0 from the database default
            $customer = Customer::create($data);

            return response()->json([
                'message' => 'Customer created successfully',
                'data'    => $customer,
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to create customer');
        }
    }

    // GET /api/customers/{customer}
    public function show(Customer $customer): JsonResponse
    {
        return response()->json(['data' => $customer]);
    }

    // PUT/PATCH /api/customers/{customer}
    public function update(Request $request, Customer $customer): JsonResponse
    {
        try {
            $data = $request->validate([
                'name'  => 'sometimes|required|string|max:255',
                'phone' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('customers', 'phone')->ignore($customer->customer_id, 'customer_id'),
                ],
                'loyalty_points' => 'sometimes|required|integer|min:0',
            ]);

            $customer->update($data);

            return response()->json([
                'message' => 'Customer updated successfully',
                'data'    => $customer,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to update customer');
        }
    }

    // DELETE /api/customers/{customer}
    public function destroy(Customer $customer): JsonResponse
    {
        try {
            if ($customer->orders()->exists()) {
                return response()->json([
                    'message' => 'Cannot delete: this customer has orders',
                ], 409);
            }

            $customer->delete();

            return response()->json(['message' => 'Customer deleted successfully']);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to delete customer');
        }
    }

    private function errorResponse(Throwable $e, string $message): JsonResponse
    {
        report($e);

        return response()->json([
            'message' => $message,
            'error'   => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}
