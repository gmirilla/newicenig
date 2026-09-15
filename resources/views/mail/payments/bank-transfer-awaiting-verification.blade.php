<x-mail::message>
# Bank transfer awaiting verification

**{{ $applicantName }}** has submitted a bank transfer payment ({{ ucfirst(str_replace('_', ' ', $context)) }}) and uploaded proof of payment.

<x-mail::table>
| | |
|:---|---:|
| Amount | {{ $payment->currency }} {{ number_format($payment->amount, 2) }} |
| Reference | {{ $payment->reference }} |
| Bank | {{ $payment->bankAccount?->bank_name ?? '—' }} |
</x-mail::table>

The proof of payment is attached to this email. Please verify it and confirm the payment in the admin panel.

<x-mail::button :url="route('filament.admin.resources.payments.index')">
Review in admin panel
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
