<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topups', function (Blueprint $table) {
            $table->uuid('topup_id')->primary();
            $table->uuid('user_id');
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['qris', 'scan_qr']);
            $table->string('payment_gateway', 50)->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->string('payment_url')->nullable();
            $table->string('payment_proof')->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->uuid('approved_by')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topups');
    }
};