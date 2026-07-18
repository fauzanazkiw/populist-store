<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Show all active carts
     */
    public function index(Request $request): View
    {
        $query = User::where('is_admin', false)
            ->whereHas('cartItems')
            ->with('cartItems.product');

        // Search by customer name or email (grouped so it doesn't bypass the filters above)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Build a cart summary per user (cart value/updated_at are derived, so we
        // compute, sort, then paginate in memory via LengthAwarePaginator).
        $cartsData = $query->get()->map(function ($user) {
            $cartItems = $user->cartItems;

            return [
                'user' => $user,
                'items_count' => $cartItems->count(),
                'total_value' => $cartItems->sum(fn($item) => $item->product?->price * $item->quantity),
                'updated_at' => $cartItems->max('updated_at'),
            ];
        });

        // Sort
        $sort = $request->input('sort', 'latest');
        $cartsData = match ($sort) {
            'oldest'     => $cartsData->sortBy('updated_at'),
            'value_high' => $cartsData->sortByDesc('total_value'),
            'value_low'  => $cartsData->sortBy('total_value'),
            default      => $cartsData->sortByDesc('updated_at'),
        };

        // Paginate the sorted collection manually (Collection has no ->paginate())
        $perPage = 15;
        $page = Paginator::resolveCurrentPage('page');
        $items = $cartsData->values();

        $carts = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        // Calculate totals
        $totalActiveCarts = User::where('is_admin', false)->whereHas('cartItems')->count();
        $totalItems = CartItem::count();
        $totalValue = CartItem::with('product')
            ->get()
            ->sum(fn($item) => $item->product?->price * $item->quantity);

        return view('admin.carts.index', compact(
            'carts',
            'totalActiveCarts',
            'totalItems',
            'totalValue'
        ));
    }

    /**
     * Show specific customer's cart
     */
    public function show(User $user): View
    {
        abort_if($user->is_admin, 404);

        $cartItems = $user->cartItems()->with('product.mainImage', 'product.category')->get();
        $totalValue = $cartItems->sum(fn($item) => $item->product?->price * $item->quantity);
        $oldestItem = $cartItems->sortBy('created_at')->first();

        return view('admin.carts.show', compact('user', 'cartItems', 'totalValue', 'oldestItem'));
    }
}
