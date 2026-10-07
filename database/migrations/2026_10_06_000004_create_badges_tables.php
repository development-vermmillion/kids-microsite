<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('icon')->default('military_tech');  // Material Symbols icon name
            $table->string('color', 20)->default('primary');   // primary | secondary | tertiary
            $table->string('image')->nullable();               // file in public/frontend/images or a full URL
            $table->boolean('show_on_home')->default(false);   // "New Badges to Earn" section
            $table->boolean('show_in_trophy_room')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('badge_rider', function (Blueprint $table) {
            $table->id();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rider_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamps();

            $table->unique(['badge_id', 'rider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_rider');
        Schema::dropIfExists('badges');
    }
};
