<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load the order items relationship
        $orders = Order::with('orderItems')->get();

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
            'order_status' => 'required|string|in:pending,processing,completed,cancelled',
            'total_amount' => 'required|numeric|min:0',
            'description'  => 'nullable|string',
        ]);

        $order = Order::create($validated);

        // Load the order items (empty initially)
        $order->load('orderItems');

        return response()->json($order, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // Load the order items relationship
        $order->load('orderItems');

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
            'order_status' => 'sometimes|required|string|in:pending,processing,completed,cancelled',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'description'  => 'nullable|string',
        ]);

        $order->update($validated);

        // Refresh and load relationships
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
