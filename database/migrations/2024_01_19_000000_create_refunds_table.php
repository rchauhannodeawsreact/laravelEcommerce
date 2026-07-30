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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('restrict');
            $table->string('refund_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->enum('reason', ['defective', 'wrong_item', 'not_as_described', 'customer_request', 'other'])->default('customer_request');
            $table->text('customer_notes')->nullable();
            $table->enum('status', ['requested', 'approved', 'rejected', 'in_transit', 'received', 'completed', 'failed'])->default('requested');
            $table->string('tracking_number')->nullable();
            $table->json('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('refund_completed_at')->nullable();
            $table->timestamps();
            $table->index('order_id');
            $table->index('vendor_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
