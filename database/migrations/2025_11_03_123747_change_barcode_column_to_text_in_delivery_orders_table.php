<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Try to drop unique constraint if it exists
        try {
            DB::statement('ALTER TABLE delivery_orders DROP INDEX delivery_orders_barcode_unique');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }

        // Try to drop regular index if it exists
        try {
            DB::statement('ALTER TABLE delivery_orders DROP INDEX delivery_orders_barcode_index');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }

        // Change column type to text
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->text('barcode')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->string('barcode')->nullable()->change();
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->unique('barcode');
            $table->index('barcode');
        });
    }
};
