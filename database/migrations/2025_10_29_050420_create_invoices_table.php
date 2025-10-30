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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamps();

            // Indexes for performance optimization
            $table->index('invoice_number');
            $table->index('sales_order_id');
            $table->index('payment_status');
            $table->index('invoice_date');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
