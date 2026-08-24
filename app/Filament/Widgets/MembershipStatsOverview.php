<?php

namespace App\Filament\Widgets;

use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\UserMembership;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MembershipStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $activeMembers = UserMembership::where('status', MembershipStatus::Active)->count();
        $pendingReview = UserMembership::where('status', MembershipStatus::PendingReview)->count();

        $revenueThisMonth = Payment::where('status', PaymentStatus::Successful)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        return [
            Stat::make('Active members', number_format($activeMembers))
                ->description('Currently active memberships')
                ->color('success'),
            Stat::make('Pending approval', number_format($pendingReview))
                ->description('Awaiting registrar review')
                ->color($pendingReview > 0 ? 'warning' : 'gray'),
            Stat::make('Revenue this month', 'NGN '.number_format($revenueThisMonth, 2))
                ->description('Successful payments, '.now()->format('F Y'))
                ->color('success'),
        ];
    }
}
