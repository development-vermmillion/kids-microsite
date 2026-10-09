<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Badge images and rider photos can be full web addresses longer than 255
     * characters. MySQL refuses those in a normal string column (SQLite didn't
     * mind), so these columns become text.
     */
    public function up(): void
    {
        Schema::table('badges', fn (Blueprint $table) => $table->text('image')->nullable()->change());
        Schema::table('riders', fn (Blueprint $table) => $table->text('avatar')->nullable()->change());
        Schema::table('rides', fn (Blueprint $table) => $table->text('proof_image')->nullable()->change());
    }

    public function down(): void
    {
        Schema::table('badges', fn (Blueprint $table) => $table->string('image')->nullable()->change());
        Schema::table('riders', fn (Blueprint $table) => $table->string('avatar')->nullable()->change());
        Schema::table('rides', fn (Blueprint $table) => $table->string('proof_image')->nullable()->change());
    }
};
