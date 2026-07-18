<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total',
        'status',
        'shipping_address',
        'payment_link',
        'payment_transaction_id',
        'paid_at',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Apply a mapped payment status to the order idempotently.
     *
     * Shared by the Midtrans webhook and the status reconciliation on the order
     * page so both surfaces transition state identically. Terminal states
     * (completed/failed) are never overwritten.
     *
     * @param  'completed'|'failed'|'pending'  $status
     */
    public function applyPaymentStatus(string $status): bool
    {
        if ($status === 'completed' && $this->status !== 'completed') {
            return $this->update(['status' => 'completed', 'paid_at' => now()]);
        }

        if ($status === 'failed' && ! in_array($this->status, ['completed', 'failed'], true)) {
            return $this->update(['status' => 'failed']);
        }

        return false;
    }
}
