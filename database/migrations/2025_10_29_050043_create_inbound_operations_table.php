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
        Schema::create('inbound_operations', function (Blueprint $table) {
            $table->id();
            $table->string('inbound_number', 50)->unique();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->dateTime('received_date');
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('inbound_number');
            $table->index('purchase_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_operations');
    }
};
