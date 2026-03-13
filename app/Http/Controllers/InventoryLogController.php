<?php

namespace App\Http\Controllers;

use App\Enums\InventoryStatus;
use App\Models\InventoryLog;
use Illuminate\Http\Request;

class InventoryLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load the menu item relationship
        $inventoryLogs = InventoryLog::with('menuItem')->get();

        return response()->json($inventoryLogs);
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
            'item_id'           => 'required|exists:menu_items,id',
            'quantity_in_stock' => 'required|numeric|min:0',
            'date_acquired'     => 'required|date',
            'expiry_date'       => 'nullable|date|after_or_equal:date_acquired',
            'description'       => 'nullable|string',
            'inventory_status'  => 'required|enum:'.implode(',', InventoryStatus::cases()),
            'is_available'      => 'required|boolean',
        ]);

        $inventoryLog = InventoryLog::create($validated);

        // Load the menu item relationship
        $inventoryLog->load('menuItem');

        return response()->json($inventoryLog, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryLog $inventoryLog)
    {
        // Load the menu item relationship
        $inventoryLog->load('menuItem');

        return response()->json($inventoryLog);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryLog $inventoryLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryLog $inventoryLog)
    {
        $validated = $request->validate([
            'item_id'           => 'sometimes|required|exists:menu_items,id',
            'quantity_in_stock' => 'sometimes|required|numeric|min:0',
            'date_acquired'     => 'sometimes|required|date',
            'expiry_date'       => 'nullable|date|after_or_equal:date_acquired',
            'description'       => 'nullable|string',
            'inventory_status'  => 'sometimes|required|enum:'.implode(',', InventoryStatus::cases()),
            'is_available'      => 'sometimes|required|boolean',
        ]);

        $inventoryLog->update($validated);

        // Refresh and load the menu item relationship
        $inventoryLog->load('menuItem');

        return response()->json($inventoryLog);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryLog $inventoryLog)
    {
        $inventoryLog->delete();

        return response()->json(null, 204);
    }
}
