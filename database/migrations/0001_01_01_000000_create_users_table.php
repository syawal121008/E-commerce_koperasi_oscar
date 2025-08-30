<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->string('full_name', 255);
            $table->string('student_id', 50)->nullable();
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->enum('role', ['customer', 'guru', 'admin']);
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->text('qr_code')->nullable();
            $table->string('profile_photo', 255)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
