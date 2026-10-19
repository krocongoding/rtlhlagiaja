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
        Schema::table('houses', function (Blueprint $table) {
            $table->string('kk_document_path')->nullable()->after('proposal_document_path');
            $table->string('akte_document_path')->nullable()->after('kk_document_path');
            $table->string('land_document_path')->nullable()->after('akte_document_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->dropColumn([
                'kk_document_path',
                'akte_document_path',
                'land_document_path',
            ]);
        });
    }
};
