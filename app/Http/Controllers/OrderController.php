<?php

namespace App\Http\Controllers;

use App\Models\InventoryLog;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    private function notifyStaff(string $title, string $message, array $data = [])
    {
        $staff = User::whereIn('role', ['admin', 'cashier'])->get();
        foreach ($staff as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => $data['type'] ?? 'info',
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ]);
        }
    }

    private function notifyCustomer(Order $order, string $title, string $message, array $data = [])
    {
        Notification::create([
            'user_id' => $order->user_id,
            'type' => $data['type'] ?? 'info',
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }


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

        $this->notifyStaff(
            'New Order',
            "Order #{$order->id} has been placed.",
            ['order_id' => $order->id, 'type' => 'new_order']
        );

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

        DB::transaction(function () use ($request, $order, $validated) {
            // If status is changing to cancelled, restore stock first
            if (isset($validated['order_status']) &&
                $validated['order_status'] === 'cancelled' &&
                $order->order_status !== 'cancelled') {
                $order->restoreStock();
            }

            $order->update($validated);
            $order->load('orderItems');
        });

        return response()->json($order);
    }

    public function updateWithItems(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_status' => 'sometimes|required|string|in:pending,preparing,ready,completed,cancelled',
            'description'  => 'nullable|string',
            'items'        => 'array',
            'items.*.inventory_id' => 'required|exists:inventory_logs,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $order, $validated) {
            // Update order fields
            if (isset($validated['order_status'])) {
                $order->order_status = $validated['order_status'];
            }
            if (array_key_exists('description', $validated)) {
                $order->description = $validated['description'];
            }
            $order->save();

            // Get existing order items
            $existingItems = $order->orderItems()->get()->keyBy('inventory_id');

            // Process incoming items
            $incoming = collect($validated['items'] ?? [])->keyBy('inventory_id');

            // Items to delete (in existing but not in incoming)
            $toDelete = $existingItems->diffKeys($incoming);
            foreach ($toDelete as $item) {
                // Restore stock manually before deleting
                $item->inventoryLog()->increment('quantity_in_stock', $item->quantity);
                $item->delete();
            }

            // Items to update or create
            foreach ($incoming as $inventoryId => $data) {
                $item = $existingItems->get($inventoryId);
                $log = InventoryLog::lockForUpdate()->find($inventoryId);
                $quantity = $data['quantity'];
                $amount = $quantity * $log->menuItem->price; // or keep as passed? we compute

                if ($item) {
                    // Update quantity if changed
                    if ($item->quantity != $quantity) {
                        // Adjust stock: difference between new and old
                        $diff = $quantity - $item->quantity;
                        if ($diff > 0) {
                            // Need more stock
                            if ($log->quantity_in_stock < $diff) {
                                throw new \Exception('Insufficient stock');
                            }
                            $log->decrement('quantity_in_stock', $diff);
                        } elseif ($diff < 0) {
                            // Return stock
                            $log->increment('quantity_in_stock', -$diff);
                        }
                        $item->quantity = $quantity;
                        $item->amount = $amount;
                        $item->save();
                    }
                } else {
                    // New item
                    if ($log->quantity_in_stock < $quantity) {
                        throw new \Exception('Insufficient stock');
                    }
                    $log->decrement('quantity_in_stock', $quantity);
                    $order->orderItems()->create([
                        'inventory_id' => $inventoryId,
                        'quantity' => $quantity,
                        'amount' => $amount,
                    ]);
                }
            }
        });

        $order->load('orderItems.inventoryLog.menuItem.category', 'user');
        return response()->json($order);
    }

    public function destroy(Order $order)
    {
        DB::transaction(function () use ($order) {
            // Restore stock before deleting the order
            $order->restoreStock();
            $order->delete();
        });
        return response()->json(null, 204);
    }

    /**
     * Update only the status of an order.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $user = $request->user();

        if ($user->role === 'customer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'order_status' => 'required|string|in:pending,preparing,ready,completed,cancelled',
        ]);

        $oldStatus = $order->order_status;

        // If changing to cancelled and order wasn't cancelled before, restore stock
        if ($validated['order_status'] === 'cancelled' && $oldStatus !== 'cancelled') {
            $order->restoreStock();
        }

        $order->update(['order_status' => $validated['order_status']]);

        // Notify the customer if the new status is preparing, ready, or cancelled
        if (in_array($validated['order_status'], ['preparing', 'ready', 'cancelled']) && $oldStatus !== $validated['order_status']) {
            $message = "Your order #{$order->id} is now {$validated['order_status']}.";
            if ($validated['order_status'] === 'ready') {
                $message = "Your order #{$order->id} is ready for pickup. Please claim at the canteen.";
            }

            Notification::create([
                'user_id' => $order->user_id,
                'type' => 'order_status_changed',
                'title' => 'Order Status Update',
                'message' => $message,
                'data' => ['order_id' => $order->id, 'status' => $validated['order_status']],
            ]);
        }

        return response()->json($order->load(['orderItems.inventoryLog.menuItem.category', 'user']));
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

    public function cancel(Request $request, Order $order)
    {
        $user = $request->user();

        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only allow cancellation if status is pending
        if ($order->order_status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be cancelled'], 400);
        }

        DB::transaction(function () use ($order) {
            // Restore stock before cancelling
            $order->restoreStock();
            $order->order_status = 'cancelled';
            $order->save();
        });

        return response()->json($order->load(['orderItems.inventoryLog.menuItem.category', 'user']));
    }

    public function customerCancel(Request $request, Order $order)
    {
        $user = $request->user();
        if ($user->id !== $order->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($order->order_status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be cancelled'], 400);
        }

        DB::transaction(function () use ($order) {
            $order->restoreStock();
            $order->update(['order_status' => 'cancelled']);
        });

        // Notify staff of cancellation
        $this->notifyStaff(
            'Order Cancelled',
            "Order #{$order->id} was cancelled by the customer.",
            ['order_id' => $order->id, 'type' => 'order_cancelled']
        );

        return response()->json($order->load(['orderItems.inventoryLog.menuItem.category', 'user']));
    }
}
