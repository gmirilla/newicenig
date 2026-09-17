<x-mail::message>
# {{ match ($context) { 'renewal' => 'Membership renewal received', 'level_change' => 'Membership level change payment received', default => 'New membership payment received' } }}

@if ($context === 'renewal')
**{{ $membership->fullName() }}** has renewed their **{{ $membership->membershipTier->name }}** membership.
@elseif ($context === 'level_change')
**{{ $membership->fullName() }}** has applied to change to the **{{ $membership->membershipTier->name }}** tier{{ $membership->previousMembership ? " (from {$membership->previousMembership->membershipTier->name})" : '' }} and paid the fee.
@else
**{{ $membership->fullName() }}** has submitted a new **{{ $membership->membershipTier->name }}** membership application and paid the application fee.
@endif

<x-mail::table>
| | |
|:---|---:|
| Amount | {{ $payment->currency }} {{ number_format($payment->amount, 2) }} |
| Reference | {{ $payment->reference }} |
| Email | {{ $membership->email }} |
| Phone | {{ $membership->phone }} |
</x-mail::table>

The member's full information sheet is attached to this email as a PDF.

@if ($context !== 'renewal')
<x-mail::button :url="route('filament.admin.resources.user-memberships.index')">
Review in admin panel
</x-mail::button>
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
