<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Hosted payment page URL returned by the gateway (Midtrans redirect_url).
            $table->string('payment_link')->nullable()->after('status');
            // Gateway transaction reference used to match incoming webhooks back to
            // a local order (Midtrans order_id).
            $table->string('payment_transaction_id')->nullable()->index()->after('payment_link');
            // Timestamp the payment was confirmed via webhook.
            $table->timestamp('paid_at')->nullable()->after('payment_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_link', 'payment_transaction_id', 'paid_at']);
        });
    }
};
