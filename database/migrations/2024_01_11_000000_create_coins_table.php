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
        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_balance', 12, 2)->default(0);
            $table->decimal('available_balance', 12, 2)->default(0);
            $table->decimal('locked_balance', 12, 2)->default(0);
            $table->decimal('pending_balance', 12, 2)->default(0);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coins');
    }
};
