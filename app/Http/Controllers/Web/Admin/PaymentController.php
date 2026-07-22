<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * List all orders/payments
     */
    public function index(Request $request): View
    {
        $query = Order::with(['user', 'items']);

        // Filter by status
        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('status', $request->input('status'));
        }

        // Search by customer name or email
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.payments.index', compact('payments'));
    }

    /**
     * Show payment/order details
     */
    public function show(Order $order): View
    {
        $order->load(['user', 'items.product']);

        return view('admin.payments.show', compact('order'));
    }

    /**
     * Update payment status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,failed,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        return redirect()->back()
            ->with('success', 'Status pembayaran berhasil diupdate.');
    }

    /**
     * Tambah/ubah nomor resi pengiriman secara manual.
     *
     * Resi diinput sendiri oleh admin (bukan dari API kurir) dan hanya boleh
     * ditambahkan setelah pelanggan menyelesaikan pembayaran.
     */
    public function updateTracking(Request $request, Order $order)
    {
        if (! $order->isPaid()) {
            return redirect()->back()
                ->with('error', 'Resi hanya dapat ditambahkan setelah pesanan dibayar.');
        }

        $validated = $request->validate([
            'shipping_courier' => 'required|string|max:255',
            'tracking_number' => 'required|string|max:255',
        ]);

        $order->update([
            'shipping_courier' => $validated['shipping_courier'],
            'tracking_number' => $validated['tracking_number'],
            'shipped_at' => $order->shipped_at ?? now(),
        ]);

        return redirect()->back()
            ->with('success', 'Nomor resi berhasil disimpan.');
    }
}
