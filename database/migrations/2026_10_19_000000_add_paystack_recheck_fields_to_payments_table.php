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
        Schema::table('payments', function (Blueprint $table) {
            // Drives the automated "is this Paystack payment still pending?"
            // retry — see App\Actions\Payments\CheckPendingPaystackPayment.
            // next_check_at is cleared once the payment resolves (or the
            // retry budget is exhausted), so the scheduled sweep only ever
            // picks up rows that are actually due.
            $table->unsignedTinyInteger('paystack_check_attempts')->default(0)->after('raw_gateway_response');
            $table->timestamp('next_paystack_check_at')->nullable()->after('paystack_check_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['paystack_check_attempts', 'next_paystack_check_at']);
        });
    }
};
