<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class TableController extends Controller
{
    // GET /api/tables?status=available
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Table::query();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return response()->json(['data' => $query->orderBy('table_id')->get()]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to fetch tables');
        }
    }

    // POST /api/tables
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'table_num' => 'required|string|max:50|unique:tables,table_num',
                'status'    => 'nullable|string|in:available,occupied,reserved',
            ]);

            $data['status'] = $data['status'] ?? 'available';

            $table = Table::create($data);

            return response()->json([
                'message' => 'Table created successfully',
                'data'    => $table,
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to create table');
        }
    }

    // GET /api/tables/{table}
    public function show(Table $table): JsonResponse
    {
        return response()->json(['data' => $table]);
    }

    // PUT/PATCH /api/tables/{table}
    public function update(Request $request, Table $table): JsonResponse
    {
        try {
            $data = $request->validate([
                'table_num' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('tables', 'table_num')->ignore($table->table_id, 'table_id'),
                ],
                'status'    => 'sometimes|required|string|in:available,occupied,reserved',
            ]);

            $table->update($data);

            return response()->json([
                'message' => 'Table updated successfully',
                'data'    => $table,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to update table');
        }
    }

    // DELETE /api/tables/{table}
    public function destroy(Table $table): JsonResponse
    {
        try {
            if ($table->orders()->exists()) {
                return response()->json([
                    'message' => 'Cannot delete: this table has orders',
                ], 409);
            }

            $table->delete();

            return response()->json(['message' => 'Table deleted successfully']);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to delete table');
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
