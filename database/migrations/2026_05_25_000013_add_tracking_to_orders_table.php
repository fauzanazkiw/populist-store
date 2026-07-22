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
            // Nama kurir/ekspedisi (contoh: JNE, J&T, SiCepat) — diisi manual oleh admin.
            $table->string('shipping_courier')->nullable()->after('paid_at');
            // Nomor resi pengiriman yang diinput sendiri oleh admin setelah pelanggan
            // menyelesaikan pembayaran. Tidak diambil dari API kurir mana pun.
            $table->string('tracking_number')->nullable()->after('shipping_courier');
            // Waktu resi ditambahkan / pesanan dikirim.
            $table->timestamp('shipped_at')->nullable()->after('tracking_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_courier', 'tracking_number', 'shipped_at']);
        });
    }
};
