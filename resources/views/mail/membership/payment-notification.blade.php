<x-mail::message>
# {{ $context === 'renewal' ? 'Membership renewal received' : 'New membership payment received' }}

@if ($context === 'renewal')
**{{ $membership->fullName() }}** has renewed their **{{ $membership->membershipTier->name }}** membership.
@else
**{{ $membership->fullName() }}** has submitted a new **{{ $membership->membershipTier->name }}** membership application and paid the registration fee.
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
