<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

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
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(null, 204);
    }
}
