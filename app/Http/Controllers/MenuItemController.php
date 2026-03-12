<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menuItems = MenuItem::with('category')->get();

        return response()->json($menuItems);
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
            'name'          => 'required|string|max:255',
            'code'          => 'nullable|string|max:50|unique:menu_items,code',
            'price'         => 'required|numeric|min:0',
            'category_id'   => 'required|exists:categories,id',
            'description'   => 'nullable|string',
        ]);

        $menuItem = MenuItem::create($validated);

        // Load the category for the response
        $menuItem->load('category');

        return response()->json($menuItem, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MenuItem $menuItem)
    {
        // Load the category relationship
        $menuItem->load('category');

        return response()->json($menuItem);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MenuItem $menuItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'name'          => 'sometimes|required|string|max:255',
            'code'          => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('menu_items')->ignore($menuItem->id),
            ],
            'price'         => 'sometimes|required|numeric|min:0',
            'category_id'   => 'sometimes|required|exists:categories,id',
            'description'   => 'nullable|string',
        ]);

        $menuItem->update($validated);

        // Refresh and load the category
        $menuItem->load('category');

        return response()->json($menuItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return response()->json(null, 204);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:menu_items,id'
        ]);

        MenuItem::whereIn('id', $request->ids)->delete();

        return response()->json(['message' => 'Items deleted successfully.']);
    }
}
