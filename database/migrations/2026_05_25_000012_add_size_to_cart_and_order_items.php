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
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('size', 8)->nullable()->after('quantity');
            $table->dropUnique(['user_id', 'product_id']);
            $table->unique(['user_id', 'product_id', 'size']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('size', 8)->nullable()->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_id', 'size']);
            $table->unique(['user_id', 'product_id']);
            $table->dropColumn('size');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('size');
        });
    }
};
