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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('review_id'); // Fixed primary key name
            $table->uuid('user_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->tinyInteger('rating')->unsigned()->comment('Rating 1-5');
            $table->text('comment')->nullable();
            $table->string('media_path')->nullable()->comment('Path to uploaded image or video');
            $table->integer('helpful_count')->default(0);
            $table->boolean('verified_purchase')->default(false);
            $table->timestamps();

            // Indexes
            $table->index(['product_id', 'created_at']);
            $table->index(['user_id', 'product_id']);
            
            // Unique constraint: satu user hanya bisa satu review per produk
            $table->unique(['user_id', 'product_id'], 'unique_user_product_review');
            
            // Foreign key constraints (opsional, sesuaikan dengan struktur DB Anda)
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};