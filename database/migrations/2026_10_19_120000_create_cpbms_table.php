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
        Schema::create('cpbms', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20)->index();
            $table->string('name');
            $table->text('address');
            $table->string('rt', 10)->nullable();
            $table->string('rw', 10)->nullable();
            $table->foreignId('district_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->string('phone', 30)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('houses', function (Blueprint $table) {
            $table->foreignId('cpbm_id')->nullable()->after('client_uuid')->constrained('cpbms')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->dropForeign(['cpbm_id']);
            $table->dropColumn('cpbm_id');
        });

        Schema::dropIfExists('cpbms');
    }
};
