<x-mail::message>
# Overdue Rent Payment Notice

Hello {{ $tenant->name ?? 'Tenant' }},

This is a friendly reminder that your rent payment for the property at **{{ $propertyAddress }}** is overdue.

We have identified the following overdue payments:

<x-mail::table>
| Due Date      | Amount Owed    |
|:-------------|:---------------|
@foreach ($payments as $payment)
| {{ \Carbon\Carbon::parse($payment->due_date)->format('Y-m-d') }} | ${{ number_format($payment->amount, 2) }} |
@endforeach
</x-mail::table>

The total outstanding amount is **${{ number_format($totalDue, 2) }}**.

Your oldest overdue payment is {{ $daysOverdue }} days past due.

Please make the payment as soon as possible. If you have already made this payment, please disregard this email.

If you have any questions or need to make arrangements, please contact us.

Thanks,
{{ config('app.name') }}
</x-mail::message>