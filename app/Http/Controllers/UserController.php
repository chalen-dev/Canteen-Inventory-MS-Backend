<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the users (admin only).
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    /**
     * Store a newly created user in storage (admin only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role'     => ['required', Rule::in(['admin', 'cashier', 'customer'])],
            'isPOS'    => 'sometimes|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        return response()->json($user, 201);
    }

    /**
     * Display the specified user (admin only).
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update the specified user in storage (admin only).
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes', 'required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'sometimes|required|string|min:8',
            'role'     => ['sometimes', 'required', Rule::in(['admin', 'cashier', 'customer'])],
            'isPOS'    => 'sometimes|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        return response()->json($user);
    }

    /**
     * Remove the specified user from storage (admin only).
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }

    public function customers()
    {
        $customers = User::where('role', 'customer')
            ->where('isPOS', false) // exclude POS users
            ->get();
        return response()->json($customers);
    }
}
