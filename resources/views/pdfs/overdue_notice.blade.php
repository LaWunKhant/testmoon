<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Overdue Payment Notice</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .container { margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .notice { margin-bottom: 25px; line-height: 1.6; }
        .details table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details th, .details td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .details th { background-color: #f2f2f2; }
        .total { font-size: 16px; font-weight: bold; text-align: right; margin-top: 20px; }
        .footer { text-align: center; margin-top: 40px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Overdue Payment Notice</h1>
            <p>{{ config('app.name') }}</p>
        </div>

        <div class="notice">
            <p>Dear {{ $tenant->name ?? 'Tenant' }},</p>
            <p>This notice is regarding the overdue rent payment for the property at:</p>
            <p><strong>{{ $propertyAddress }}</strong></p>
            <p>Your payment was due on {{ \Carbon\Carbon::parse($payments->sortBy('due_date')->first()->due_date)->format('Y-m-d') }}.</p>
            <p>We have identified the following overdue payment(s):</p>
        </div>

        <div class="details">
            <table>
                <thead>
                    <tr>
                        <th>Due Date</th>
                        <th>Amount Owed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $payment)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($payment->due_date)->format('Y-m-d') }}</td>
                        <td>${{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

         <div class="total">
            Total Outstanding Amount: ${{ number_format($totalDue, 2) }}
        </div>

         @if ($daysOverdue > 0)
         <p style="text-align: right; font-size: 14px; margin-top: 10px;">This payment is {{ $daysOverdue }} days past due.</p>
         @endif


        <div class="notice" style="margin-top: 30px;">
            <p>Please make the outstanding payment as soon as possible to avoid further action.</p>
            <p>If you have already made this payment, please disregard this notice.</p>
            <p>If you have any questions or need to make payment arrangements, please contact us immediately.</p>
        </div>


        <div class="footer">
            <p>This is an automated notice.</p>
            <p>Contact us at [Your Contact Information]</p> <!-- Add your contact info here -->
        </div>
    </div>
</body>
</html>