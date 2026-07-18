<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconcile a legacy column name left over from the old "Mayar" gateway.
 *
 * On databases provisioned before the switch to Midtrans, migration 000008 ran
 * while it still created `mayar_transaction_id`; the file was later edited to
 * `payment_transaction_id` but the already-migrated schema kept the old name.
 * Fresh databases (and the SQLite test DB) already have the correct column, so
 * this rename is guarded to run only where the legacy column is present.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'mayar_transaction_id')
            && ! Schema::hasColumn('orders', 'payment_transaction_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('mayar_transaction_id', 'payment_transaction_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'payment_transaction_id')
            && ! Schema::hasColumn('orders', 'mayar_transaction_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('payment_transaction_id', 'mayar_transaction_id');
            });
        }
    }
};
