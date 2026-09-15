<x-mail::message>
# Payment submission received

Hi {{ $applicantName }},

We've received your bank transfer details and proof of payment.

<x-mail::table>
| | |
|:---|---:|
| Amount | {{ $payment->currency }} {{ number_format($payment->amount, 2) }} |
| Reference | {{ $payment->reference }} |
</x-mail::table>

Our team will verify it shortly — you'll receive another email once it's confirmed.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
