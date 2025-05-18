<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')->unique()->after('id');
            }
            if (!Schema::hasColumn('orders', 'order_date')) {
                $table->timestamp('order_date')->after('order_status');
            }
            if (!Schema::hasColumn('orders', 'delivery_date')) {
                $table->timestamp('delivery_date')->nullable()->after('order_date');
            }
            if (!Schema::hasColumn('orders', 'buyer_id')) {
                $table->foreignId('buyer_id')->after('delivery_date')->constrained('users');
            }
            if (!Schema::hasColumn('orders', 'center_id')) {
                $table->foreignId('center_id')->after('buyer_id')->constrained('users');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_number',
                'order_date',
                'delivery_date',
                'buyer_id',
                'center_id'
            ]);
        });
    }
}; 