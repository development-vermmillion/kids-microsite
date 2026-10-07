<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('icon')->default('flag');           // Material Symbols icon name
            $table->string('color', 20)->default('primary');   // primary | secondary | tertiary
            $table->unsignedInteger('reward_points')->default(0);
            $table->decimal('target_value', 8, 2);
            $table->string('unit')->nullable();                // e.g. "km"; null for plain counts
            $table->string('progress_label')->default('Progress');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('challenge_rider', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rider_id')->constrained()->cascadeOnDelete();
            $table->decimal('progress_value', 8, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['challenge_id', 'rider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_rider');
        Schema::dropIfExists('challenges');
    }
};
