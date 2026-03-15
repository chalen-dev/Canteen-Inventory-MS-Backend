<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get summary cards data.
     */
    public function summary(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Order::query();

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->with('orderItems')->get();

        $totalSales = $orders->sum(function ($order) {
            return $order->orderItems->sum('amount');
        });
        $totalOrders = $orders->count();
        $avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        return response()->json([
            'total_sales' => round($totalSales, 2),
            'total_orders' => $totalOrders,
            'avg_order_value' => round($avgOrderValue, 2),
        ]);
    }

    /**
     * Get sales grouped by day/week/month.
     */
    public function salesByPeriod(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'group_by' => 'in:day,week,month',
        ]);

        $groupBy = $request->group_by ?? 'day';

        $query = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->select(
                DB::raw($this->getDateTruncSql($groupBy, 'orders.created_at') . ' as date'),
                DB::raw('SUM(order_items.amount) as total_sales')
            )
            ->groupBy('date');

        if ($request->start_date) {
            $query->whereDate('orders.created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('orders.created_at', '<=', $request->end_date);
        }

        $results = $query->orderBy('date')->get();

        return response()->json($results);
    }

    /**
     * Get best-selling items.
     */
    public function bestSellingItems(Request $request)
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'in:quantity,revenue',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $limit = $request->limit ?? 5;
        $sortBy = $request->sort_by ?? 'revenue';

        $query = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('inventory_logs', 'inventory_logs.id', '=', 'order_items.inventory_id')
            ->join('menu_items', 'menu_items.id', '=', 'inventory_logs.item_id')
            ->select(
                'menu_items.id',
                'menu_items.name',
                'menu_items.code',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.amount) as total_revenue')
            )
            ->groupBy('menu_items.id', 'menu_items.name', 'menu_items.code');

        if ($request->start_date) {
            $query->whereDate('orders.created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('orders.created_at', '<=', $request->end_date);
        }

        $results = $query->orderByDesc('total_' . $sortBy)
            ->limit($limit)
            ->get();

        return response()->json($results);
    }

    /**
     * Get sales by category.
     */
    public function salesByCategory(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('inventory_logs', 'inventory_logs.id', '=', 'order_items.inventory_id')
            ->join('menu_items', 'menu_items.id', '=', 'inventory_logs.item_id')
            ->join('categories', 'categories.id', '=', 'menu_items.category_id')
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_items.amount) as total_sales')
            )
            ->groupBy('categories.id', 'categories.name');

        if ($request->start_date) {
            $query->whereDate('orders.created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('orders.created_at', '<=', $request->end_date);
        }

        $results = $query->orderByDesc('total_sales')->get();

        return response()->json($results);
    }

    /**
     * Get order volume trend.
     */
    public function orderVolume(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as order_count')
        )
            ->groupBy('date');

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $results = $query->orderBy('date')->get();

        return response()->json($results);
    }

    /**
     * Helper to get DATE_TRUNC SQL for different databases.
     * Assuming MySQL.
     */
    private function getDateTruncSql($groupBy, $column)
    {
        switch ($groupBy) {
            case 'week':
                return "DATE_FORMAT($column, '%Y-%u')"; // year-week number
            case 'month':
                return "DATE_FORMAT($column, '%Y-%m')";
            case 'day':
            default:
                return "DATE($column)";
        }
    }
}
