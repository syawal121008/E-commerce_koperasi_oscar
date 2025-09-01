<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('transaction_id')->primary();
            $table->uuid('user_id');
            $table->enum('type', ['topup', 'payment', 'refund', 'income', 'expense']);
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'success','paid', 'completed', 'failed'])->default('pending');
            $table->uuid('related_id')->nullable();
            $table->text('description')->nullable(); 
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
