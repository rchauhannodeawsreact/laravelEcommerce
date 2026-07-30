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
        Schema::create('coin_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['earning', 'redemption', 'transfer_sent', 'transfer_received', 'referral', 'admin_adjustment', 'expiry'])->default('earning');
            $table->decimal('amount', 12, 2);
            $table->text('description');
            $table->string('reference_type')->nullable(); // order, referral, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled', 'failed'])->default('completed');
            $table->timestamps();
            $table->index('user_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coin_transactions');
    }
};
