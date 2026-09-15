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
        Schema::create('membership_tier_notice', function (Blueprint $table) {
            $table->foreignId('notice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('membership_tier_id')->constrained()->cascadeOnDelete();
            $table->primary(['notice_id', 'membership_tier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_tier_notice');
    }
};
