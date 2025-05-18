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
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_type',20)->nullable()->change();
            $table->string('unit',191)->nullable()->change();
            $table->integer('min_qty')->length(11)->default(1)->nullable()->change();
            $table->boolean('refundable')->default(true)->nullable()->change();
            $table->text('color_image')->nullable()->change();
            $table->boolean('variant_product')->default(false)->nullable()->change();
            $table->boolean('published')->default(false)->nullable()->change();
            $table->double('unit_price')->default(0)->nullable()->change();
            $table->double('purchase_price')->default(0)->nullable()->change();
            $table->string('tax',191)->default('0.00')->nullable()->change();
            $table->string('tax_model',20)->default('exclude')->nullable()->change();
            $table->string('discount',191)->default('0.00')->nullable()->change();
            $table->integer('minimum_order_qty')->length(11)->default(1)->nullable()->change();
            $table->boolean('free_shipping')->default(false)->nullable()->change();
            $table->boolean('status')->default(true)->nullable()->change();
            $table->boolean('featured_status')->default(true)->nullable()->change();
            $table->boolean('request_status')->default(false)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_type',20)->nullable(false)->change();
            $table->string('unit',191)->nullable(false)->change();
            $table->boolean('refundable')->default(true)->nullable(false)->change();
            $table->text('color_image')->nullable(false)->change();
            $table->boolean('variant_product')->default(false)->nullable(false)->change();
            $table->boolean('published')->default(false)->nullable(false)->change();
            $table->double('unit_price')->default(0)->nullable(false)->change();
            $table->double('purchase_price')->default(0)->nullable(false)->change();
            $table->string('tax',191)->default('0.00')->nullable(false)->change();
            $table->string('tax_model',20)->default('exclude')->nullable(false)->change();
            $table->string('discount',191)->default('0.00')->nullable(false)->change();
            $table->integer('minimum_order_qty')->length(11)->default(1)->nullable(false)->change();
            $table->boolean('free_shipping')->default(false)->nullable(false)->change();
            $table->boolean('status')->default(true)->nullable(false)->change();
            $table->boolean('featured_status')->default(true)->nullable(false)->change();
            $table->boolean('request_status')->default(false)->nullable(false)->change(); 
        });
    }
};
