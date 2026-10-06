<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kids who use the microsite (the public "riders").
     * Admin users stay in the default `users` table.
     */
    public function up(): void
    {
        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile', 15)->unique();
            $table->string('avatar')->nullable();
            $table->unsignedInteger('level')->default(1);
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riders');
    }
};
