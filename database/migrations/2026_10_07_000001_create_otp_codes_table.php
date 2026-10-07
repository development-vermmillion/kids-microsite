<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One-time passwords sent to a mobile number (for login, joining and ride uploads).
     * Kept separate from riders because new numbers don't have a rider yet.
     */
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 15)->unique();
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        // The unused OTP columns on riders are replaced by the table above.
        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn(['otp', 'otp_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
        });

        Schema::dropIfExists('otp_codes');
    }
};
