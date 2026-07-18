<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * List all customers
     */
    public function index(Request $request): View
    {
        $query = User::where('is_admin', false);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        }

        $customers = $query->withCount('cartItems', 'orders')
            ->paginate(15);

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show customer details
     */
    public function show(User $customer): View
    {
        abort_if($customer->is_admin, 404);

        $customer->load(['cartItems.product', 'orders.items']);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Delete a customer
     */
    public function destroy(User $customer)
    {
        abort_if($customer->is_admin, 404);

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }
}
