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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index('order_date');
            $table->index('expected_date');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->index('order_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['order_date']);
            $table->dropIndex(['expected_date']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex(['order_date']);
        });
    }
};
