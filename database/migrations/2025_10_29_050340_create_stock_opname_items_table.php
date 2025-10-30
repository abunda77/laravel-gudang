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
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('system_stock')->default(0)->comment('Stock quantity from system calculation');
            $table->integer('physical_stock')->default(0)->comment('Actual counted stock quantity');
            $table->integer('variance')->default(0)->comment('Difference: physical_stock - system_stock');
            $table->timestamps();

            // Indexes for performance optimization
            $table->index('stock_opname_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
    }
};
