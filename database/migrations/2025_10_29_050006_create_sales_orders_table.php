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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('so_number', 50)->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('order_date');
            $table->enum('status', ['draft', 'approved', 'partially_fulfilled', 'completed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->foreignId('sales_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('so_number');
            $table->index('customer_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
