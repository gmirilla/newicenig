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
        Schema::create('membership_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('abbreviation')->nullable();
            $table->text('description')->nullable();
            $table->decimal('registration_fee', 12, 2)->default(0);
            $table->decimal('renewal_fee', 12, 2)->default(0);
            $table->string('currency', 3)->default('NGN');
            $table->boolean('requires_qualification_upload')->default(false);
            $table->boolean('requires_employer_info')->default(false);
            $table->unsignedTinyInteger('min_years_experience')->nullable();
            $table->json('benefits')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_tiers');
    }
};
