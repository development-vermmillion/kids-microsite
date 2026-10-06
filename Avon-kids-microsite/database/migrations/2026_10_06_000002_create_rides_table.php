<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ride submissions from the "Upload Ride" form, reviewed by an admin.
     */
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->date('ride_date');
            $table->time('ride_time')->nullable();
            $table->decimal('distance_km', 8, 2);
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('proof_image')->nullable();
            $table->string('status', 20)->default('pending'); // pending | verified | rejected
            $table->string('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['rider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
