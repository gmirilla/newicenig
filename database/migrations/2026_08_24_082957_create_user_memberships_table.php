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
        Schema::create('user_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('membership_tier_id')->constrained();
            $table->string('membership_number')->nullable()->unique();
            $table->string('status')->default('pending_payment');

            // Applicant details common across most tiers.
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('employer_name')->nullable();
            $table->string('job_title')->nullable();
            $table->unsignedTinyInteger('years_of_experience')->nullable();
            $table->string('state_of_origin')->nullable();
            $table->string('qualification')->nullable();
            $table->date('date_of_birth')->nullable();

            // Tier-specific data that doesn't warrant a dedicated column.
            $table->json('extra_fields')->nullable();

            $table->boolean('directory_opt_in')->default(false);

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('previous_membership_id')->nullable()->constrained('user_memberships')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_memberships');
    }
};
