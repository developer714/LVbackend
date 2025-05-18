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
            $table->string('payment_status', 15)->default('unpaid')->nullable()->change();
            $table->string('order_status', 50)->default('pending')->nullable()->change();
            $table->double('order_amount')->default(0)->nullable()->change();
            $table->decimal('admin_commission', 8, 2)->default(0.00)->nullable()->change();
            $table->string('is_pause', 20)->default(0)->nullable()->change();
            $table->double('discount_amount')->default(0)->nullable()->change();
            $table->string('coupon_discount_bearer', 191)->default('inhouse')->nullable()->change();
            $table->bigInteger('shipping_method_id', false, 20)->default(0)->nullable()->change();
            $table->decimal('shipping_cost', 8, 2)->default(0.00)->nullable()->change();
            $table->smallInteger('is_shipping_free')->default(0)->nullable()->change();
            $table->string('order_group_id', 191)->default('def-order-group')->nullable()->change();
            $table->string('verification_code', 191)->default(0)->nullable()->change();
            $table->smallInteger('verification_status')->default(0)->nullable()->change();
            $table->double('deliveryman_charge')->default(0)->nullable()->change();
            $table->text('billing_address_data')->nullable()->change();
            $table->string('order_type', 191)->default('default_type')->nullable()->change();
            $table->decimal('extra_discount', 8, 2)->default(0.00)->nullable()->change(); 
            $table->smallInteger('checked')->default(0)->nullable()->change();
            $table->smallInteger('is_guest')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status', 15)->default('unpaid')->nullable(false)->change();
            $table->string('order_status', 50)->default('pending')->nullable(false)->change();
            $table->double('order_amount')->default(0)->nullable(false)->change();
            $table->decimal('admin_commission', 8, 2)->default(0.00)->nullable(false)->change();
            $table->string('is_pause', 20)->default(0)->nullable(false)->change();
            $table->double('discount_amount')->default(0)->nullable(false)->change();
            $table->string('coupon_discount_bearer', 191)->default('inhouse')->nullable(false)->change();
            $table->bigInteger('shipping_method_id', false, 20)->default(0)->nullable(false)->change();
            $table->decimal('shipping_cost', 8, 2)->default(0.00)->nullable(false)->change();
            $table->integer('is_shipping_free')->default(0)->nullable(false)->change();
            $table->string('order_group_id', 191)->default('def-order-group')->nullable(false)->change();
            $table->string('verification_code', 191)->default(0)->nullable(false)->change();
            $table->integer('verification_status')->default(0)->nullable(false)->change();
            $table->double('deliveryman_charge')->default(0)->nullable(false)->change();
            $table->text('billing_address_data')->nullable(false)->change();
            $table->string('order_type', 191)->default('default_type')->nullable(false)->change();
            $table->decimal('extra_discount', 8, 2)->default(0.00)->nullable(false)->change(); 
            $table->integer('checked')->default(0)->nullable(false)->change();
            $table->integer('is_guest')->default(0)->nullable(false)->change();
        });
    }
};
