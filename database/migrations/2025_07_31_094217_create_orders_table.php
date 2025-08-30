<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('order_id')->primary();
            $table->uuid('customer_id');
            $table->uuid('admin_id');
            $table->string('payment_method');
            $table->decimal('total_price', 12, 2);
            $table->decimal('balance_used', 12, 2)->default(0);
            $table->decimal('cash_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'paid', 'cancelled','completed'])->default('pending');
            $table->timestamps();

            $table->foreign('customer_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('admin_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
