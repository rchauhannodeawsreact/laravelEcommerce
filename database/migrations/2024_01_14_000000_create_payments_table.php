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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('transaction_id')->unique();
            $table->string('paytm_transaction_id')->nullable()->unique();
            $table->string('merchant_reference')->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['paytm_wallet', 'paytm_netbanking', 'paytm_card', 'paytm_upi', 'wallet'])->default('paytm_card');
            $table->enum('status', ['initiated', 'pending', 'completed', 'failed', 'cancelled'])->default('initiated');
            $table->json('response_data')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_transaction_id')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            $table->index('transaction_id');
            $table->index('user_id');
            $table->index('order_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
