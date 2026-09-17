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
        Schema::table('member_files', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_file_id')->nullable()->unique()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_files', function (Blueprint $table) {
            $table->dropColumn('legacy_file_id');
        });
    }
};
