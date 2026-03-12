<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load all relationships
        $orderItems = OrderItem::with(['order', 'menuItem', 'user'])->get();

        return response()->json($orderItems);
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
            'order_id'     => 'required|exists:orders,id',
            'menu_item_id' => 'required|exists:menu_items,id',
            'user_id'      => 'required|exists:users,id',
            'quantity'     => 'required|integer|min:1',
            'amount'       => 'required|numeric|min:0',
        ]);

        $orderItem = OrderItem::create($validated);

        // Load relationships
        $orderItem->load(['order', 'menuItem', 'user']);

        return response()->json($orderItem, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderItem $orderItem)
    {
        // Load relationships
        $orderItem->load(['order', 'menuItem', 'user']);

        return response()->json($orderItem);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrderItem $orderItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrderItem $orderItem)
    {
        $validated = $request->validate([
            'order_id'     => 'sometimes|required|exists:orders,id',
            'menu_item_id' => 'sometimes|required|exists:menu_items,id',
            'user_id'      => 'sometimes|required|exists:users,id',
            'quantity'     => 'sometimes|required|integer|min:1',
            'amount'       => 'sometimes|required|numeric|min:0',
        ]);

        $orderItem->update($validated);

        // Refresh and load relationships
        $orderItem->load(['order', 'menuItem', 'user']);

        return response()->json($orderItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderItem $orderItem)
    {
        $orderItem->delete();

        return response()->json(null, 204);
    }
}
