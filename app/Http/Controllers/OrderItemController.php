<?php

namespace App\Http\Controllers;

use App\Models\InventoryLog;
use App\Models\OrderItem;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load all relationships
        $orderItems = OrderItem::with(['order', 'inventoryLog.menuItem'])->get();

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
            'inventory_id' => 'required|exists:inventory_logs,id',
            'quantity'     => 'required|integer|min:1',
            'amount'       => 'required|numeric|min:0',
        ]);

        try {
            $orderItem = OrderItem::create($validated);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        // Load relationships
        $orderItem->load(['order', 'inventoryLog.menuItem']);

        return response()->json($orderItem, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderItem $orderItem)
    {
        // Load relationships
        $orderItem->load(['order', 'inventoryLog.menuItem']);

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
            'inventory_id' => 'sometimes|required|exists:inventory_logs,id',
            'quantity'     => 'sometimes|required|integer|min:1',
            'amount'       => 'sometimes|required|numeric|min:0',
        ]);

        $orderItem->update($validated);

        $orderItem->load(['order', 'inventoryLog.menuItem']);

        return response()->json($orderItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderItem $orderItem) // NOT InventoryLog
    {
        $orderItem->delete();
        return response()->json(null, 204);
    }
}
