<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('order_details', 'product_name')) {
                $table->string('product_name')->after('product_id');
            }
            if (!Schema::hasColumn('order_details', 'pv')) {
                $table->decimal('pv', 10, 2)->after('price');
            }
            if (!Schema::hasColumn('order_details', 'quantity')) {
                $table->integer('quantity')->after('pv');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn([
                'product_name',
                'pv',
                'quantity'
            ]);
        });
    }
}; 