<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riders now register with a username and a verified email address, and log
     * in with an OTP sent to that email. The mobile number is kept as contact
     * information only, so brothers and sisters may share a parent's number.
     */
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->string('username', 30)->nullable()->unique()->after('name');
            $table->string('email')->nullable()->unique()->after('username');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->dropUnique(['mobile']);
        });

        // OTPs are now sent to an email address, for registering or logging in.
        Schema::dropIfExists('otp_codes');
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('purpose', 20); // register | login
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->unique(['email', 'purpose']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 15)->unique();
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropUnique(['email']);
            $table->dropColumn(['username', 'email', 'email_verified_at']);
            $table->unique('mobile');
        });
    }
};
