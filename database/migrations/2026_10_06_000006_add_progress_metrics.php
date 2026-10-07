<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets challenges and badges track progress automatically from verified rides.
     *
     * metric: manual | distance | rides | ride_days | morning_rides
     */
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->string('metric', 20)->default('manual')->after('progress_label');
            $table->foreignId('badge_id')->nullable()->after('metric')->constrained()->nullOnDelete();
        });

        Schema::table('badges', function (Blueprint $table) {
            $table->string('metric', 20)->default('manual')->after('image');
            $table->decimal('target_value', 8, 2)->nullable()->after('metric');
        });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('badge_id');
            $table->dropColumn('metric');
        });

        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn(['metric', 'target_value']);
        });
    }
};
