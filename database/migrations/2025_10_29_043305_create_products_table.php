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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 100)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit', 50);
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->integer('minimum_stock')->default(0);
            $table->string('rack_location', 100)->nullable();
            $table->timestamps();

            $table->index('sku');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
