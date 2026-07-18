<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\CartItem;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard
     */
    public function index(): View
    {
        $totalCustomers = User::where('is_admin', false)->count();
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total');
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalCartItems = CartItem::count();

        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'totalCustomers',
            'totalOrders',
            'totalRevenue',
            'pendingOrders',
            'totalCartItems',
            'recentOrders'
        ));
    }
}
