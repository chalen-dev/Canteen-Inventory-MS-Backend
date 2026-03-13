<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'photo'         => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'description'   => 'nullable|string',
        ]);

        // Handle file upload or use default image
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('menu_items', 'public');
            $validated['photo_path'] = $path;
        } else {
            // Define default image paths
            $defaultSource = database_path('seeders/images/default/default_img.png');
            $defaultDest = 'menu_items/default.png'; // Fixed name for default image

            // Check if default image already exists in public storage
            if (!Storage::disk('public')->exists($defaultDest)) {
                // If source exists, copy it
                if (file_exists($defaultSource)) {
                    Storage::disk('public')->put($defaultDest, file_get_contents($defaultSource));
                }
            }

            // Set the default path if the file now exists (or we assume it does)
            if (Storage::disk('public')->exists($defaultDest)) {
                $validated['photo_path'] = $defaultDest;
            } else {
                // Fallback: no image
                $validated['photo_path'] = null;
            }
        }

        $menuItem = MenuItem::create($validated);
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
            'photo'         => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description'   => 'nullable|string',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists and it's not the default
            if ($menuItem->photo_path && $menuItem->photo_path !== 'menu_items/default.png') {
                Storage::disk('public')->delete($menuItem->photo_path);
            }
            // Store new photo
            $path = $request->file('photo')->store('menu_items', 'public');
            $validated['photo_path'] = $path;
        }
        // Handle explicit removal (photo field present and null)
        elseif ($request->has('photo') && $request->get('photo') === null) {
            // Delete old photo if it's not the default
            if ($menuItem->photo_path && $menuItem->photo_path !== 'menu_items/default.png') {
                Storage::disk('public')->delete($menuItem->photo_path);
            }
            // Ensure default image exists in public storage
            $defaultSource = database_path('seeders/images/default/default_img.png');
            $defaultDest = 'menu_items/default.png';
            if (!Storage::disk('public')->exists($defaultDest)) {
                if (file_exists($defaultSource)) {
                    Storage::disk('public')->put($defaultDest, file_get_contents($defaultSource));
                }
            }
            // Assign default path (or null if default missing)
            $validated['photo_path'] = Storage::disk('public')->exists($defaultDest) ? $defaultDest : null;
        }

        $menuItem->update($validated);
        $menuItem->load('category');

        return response()->json($menuItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MenuItem $menuItem)
    {
        if ($menuItem->photo_path) {
            Storage::disk('public')->delete($menuItem->photo_path);
        }

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
