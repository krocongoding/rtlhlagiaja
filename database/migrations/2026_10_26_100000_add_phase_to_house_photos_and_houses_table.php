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
        Schema::table('house_photos', function (Blueprint $table) {
            $table->string('phase', 20)->default('before')->index()->after('type');
        });

        Schema::table('houses', function (Blueprint $table) {
            $table->integer('realized_score')->nullable()->after('realization_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->dropColumn(['realized_score']);
        });

        Schema::table('house_photos', function (Blueprint $table) {
            $table->dropIndex(['phase']);
            $table->dropColumn(['phase']);
        });
    }
};
