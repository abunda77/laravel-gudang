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
        Schema::create('outbound_operations', function (Blueprint $table) {
            $table->id();
            $table->string('outbound_number', 50)->unique();
            $table->foreignId('sales_order_id')->constrained()->onDelete('cascade');
            $table->dateTime('shipped_date');
            $table->text('notes')->nullable();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('outbound_number');
            $table->index('sales_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbound_operations');
    }
};
