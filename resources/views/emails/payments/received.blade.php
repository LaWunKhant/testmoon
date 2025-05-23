<x-mail::message>
# Payment Received Confirmation

Hello {{ $tenant->name ?? 'Tenant' }},

This email confirms that we have received your payment of **${{ number_format($payment->amount, 2) }}** on {{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}.

Thank you for your prompt payment!

If you have any questions, please do not hesitate to contact us.

Thanks,
{{ config('app.name') }}
</x-mail::message>