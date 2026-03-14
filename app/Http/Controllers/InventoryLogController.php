<?php

namespace App\Http\Controllers;

use App\Enums\InventoryStatus;
use App\Models\InventoryLog;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load menu item and its category
        $inventoryLogs = InventoryLog::with('menuItem.category')->get();

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
            'inventory_status' => 'required|enum:'.implode(',', array_column(InventoryStatus::cases(), 'value')),
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
            'inventory_status' => 'required|enum:'.implode(',', array_column(InventoryStatus::cases(), 'value')),
            'is_available'      => 'sometimes|required|boolean',
        ]);

        $inventoryLog->update($validated);

        // Refresh and load the menu item relationship
        $inventoryLog->load('menuItem');

        return response()->json($inventoryLog);
    }

    public function updateQuantity(Request $request, InventoryLog $inventoryLog)
    {
        $request->validate([
            'quantity_in_stock' => 'required|numeric|min:0',
        ]);

        $inventoryLog->quantity_in_stock = $request->quantity_in_stock;
        $inventoryLog->save();

        $inventoryLog->load('menuItem');

        return response()->json($inventoryLog);
    }

    public function toggleAvailability(InventoryLog $inventoryLog, Request $request)
    {
        $request->validate([
            'is_available' => 'required|boolean',
        ]);

        $inventoryLog->is_available = $request->is_available;
        $inventoryLog->save();

        // Optionally load relationship
        $inventoryLog->load('menuItem');

        return response()->json($inventoryLog);
    }

    public function bulkToggleAvailability(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:inventory_logs,id',
            'is_available' => 'required|boolean',
        ]);

        $count = InventoryLog::whereIn('id', $request->ids)
            ->update(['is_available' => $request->is_available]);

        return response()->json([
            'message' => "{$count} inventory log(s) updated successfully.",
            'updated_count' => $count
        ]);
    }

    public function bulkArchive(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:inventory_logs,id',
        ]);

        $count = InventoryLog::whereIn('id', $request->ids)
            ->update([
                'is_archived' => true,
                'is_available' => false
            ]);

        return response()->json([
            'message' => "{$count} inventory log(s) archived successfully.",
            'archived_count' => $count
        ]);
    }

    public function bulkUnarchive(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:inventory_logs,id',
        ]);

        $count = InventoryLog::whereIn('id', $request->ids)
            ->update(['is_archived' => false]);

        return response()->json([
            'message' => "{$count} inventory log(s) unarchived successfully.",
            'unarchived_count' => $count
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryLog $inventoryLog)
    {
        try {
            $inventoryLog->delete();
            return response()->json(null, 204);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1451) {
                return response()->json([
                    'message' => 'Cannot delete this inventory log because it is referenced in order items.'
                ], 409);
            }
            throw $e;
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:inventory_logs,id'
        ]);

        DB::beginTransaction();
        try {
            InventoryLog::whereIn('id', $request->ids)->delete();
            DB::commit();
            return response()->json(['message' => 'Inventory logs deleted successfully.']);
        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->errorInfo[1] == 1451) {
                return response()->json([
                    'message' => 'Cannot delete one or more inventory logs because they are referenced in order items.'
                ], 409);
            }
            throw $e;
        }
    }


}
