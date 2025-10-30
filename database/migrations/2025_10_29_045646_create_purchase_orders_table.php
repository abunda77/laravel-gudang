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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 50)->unique();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->enum('status', ['draft', 'sent', 'partially_received', 'completed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('po_number');
            $table->index('supplier_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
