<x-mail::message>
# Payment still unconfirmed

Paystack has not confirmed reference **{{ $payment->reference }}** as successful, failed, or abandoned after 5 automated checks over the last 2.5 minutes. This can happen when Paystack itself is slow to settle a transaction, but it needs a person to look at it now.

<x-mail::table>
| | |
|:---|---:|
| Amount | {{ $payment->currency }} {{ number_format($payment->amount, 2) }} |
| Reference | {{ $payment->reference }} |
| Status | {{ $payment->status->label() }} |
</x-mail::table>

Check this reference in the Paystack dashboard, or use "Recheck with Paystack" on this payment in the admin panel to try again.

<x-mail::button :url="route('filament.admin.resources.payments.index')">
Review in admin panel
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
