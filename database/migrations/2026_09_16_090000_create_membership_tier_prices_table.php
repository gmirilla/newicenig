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
        Schema::create('membership_tier_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_tier_id')->constrained()->cascadeOnDelete();
            $table->string('currency', 3);
            $table->decimal('registration_fee', 12, 2)->nullable();
            $table->decimal('renewal_fee', 12, 2)->nullable();
            $table->timestamps();

            $table->unique(['membership_tier_id', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_tier_prices');
    }
};
