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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('do_number', 50)->unique();
            $table->foreignId('outbound_operation_id')->constrained('outbound_operations')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->dateTime('delivery_date');
            $table->string('recipient_name')->nullable();
            $table->text('notes')->nullable();
            $table->string('barcode')->nullable()->unique();
            $table->timestamps();

            // Indexes for performance optimization
            $table->index('do_number');
            $table->index('outbound_operation_id');
            $table->index('barcode');
            $table->index('delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
