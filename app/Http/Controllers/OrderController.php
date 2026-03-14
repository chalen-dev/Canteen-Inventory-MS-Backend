<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Order::with([
            'orderItems.inventoryLog.menuItem.category',
            'user'
        ]);

        if ($user->role === 'customer') {
            $query->where('user_id', $user->id);
        }

        $orders = $query->get();
        return response()->json($orders);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_status' => 'required|string|in:pending,preparing,ready,completed,cancelled',
            'description'  => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;

        $order = Order::create($validated);
        $order->load('orderItems'); // load empty collection for consistency

        return response()->json($order, 201);
    }

    /**
     * Store a new order for a specified customer (admin/cashier only).
     */
    public function storeForCustomerByStaff(Request $request)
    {
        $validated = $request->validate([
            'order_status' => 'required|string|in:pending,preparing,ready,completed,cancelled',
            'description'  => 'nullable|string',
            'user_id'      => 'required|exists:users,id',
        ]);

        $order = Order::create([
            'user_id'      => $validated['user_id'],
            'order_status' => $validated['order_status'],
            'description'  => $validated['description'] ?? null,
        ]);

        $order->load('orderItems');
        return response()->json($order, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Order $order)
    {
        $user = $request->user();
        if ($user->role === 'customer' && $order->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $order->load([
            'orderItems.inventoryLog.menuItem.category',
            'user'
        ]);
        return response()->json($order);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $orders)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_status' => 'sometimes|required|string|in:pending,preparing,ready,completed,cancelled',
            'description'  => 'nullable|string',
        ]);

        $order->update($validated);
        $order->load('orderItems');

        return response()->json($order);
    }

    /**
     * Update only the status of an order.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $user = $request->user();

        // Customers cannot update status
        if ($user->role === 'customer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'order_status' => 'required|string|in:pending,preparing,ready,completed,cancelled',
        ]);

        $order->update(['order_status' => $validated['order_status']]);

        return response()->json($order->load(['orderItems.inventoryLog.menuItem.category', 'user']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(null, 204);
    }

    /**
     * Delete multiple orders.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:orders,id'
        ]);

        // Only admin can bulk delete (already in admin group, but double-check)
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();
            $count = Order::whereIn('id', $request->ids)->delete();
            DB::commit();
            return response()->json(['message' => "$count orders deleted successfully."]);
        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->errorInfo[1] == 1451) {
                return response()->json([
                    'message' => 'Cannot delete one or more orders because they have associated order items.'
                ], 409);
            }
            throw $e;
        }
    }
}
