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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->comment('Positive for inbound/adjustment_plus, negative for outbound/adjustment_minus');
            $table->enum('type', ['inbound', 'outbound', 'adjustment_plus', 'adjustment_minus']);
            $table->string('reference_type')->nullable()->comment('Polymorphic relation type');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('Polymorphic relation id');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for performance optimization
            $table->index('product_id');
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
