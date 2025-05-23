<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; } /* DejaVu Sans often needed for special characters in PDF */
        .container { margin: 20px; }
        .header { text-align: center; margin-bottom: 40px; }
        .details { margin-bottom: 30px; }
        .details p { margin: 5px 0; }
        .amount { font-size: 18px; font-weight: bold; text-align: center; margin-top: 30px; }
        .footer { text-align: center; margin-top: 50px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Payment Receipt</h1>
            <p>{{ config('app.name') }}</p>
        </div>

        <div class="details">
            <p><strong>Receipt Number:</strong> {{ $payment->id }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</p>
            <p><strong>Received From:</strong> {{ $tenant->name ?? 'Tenant' }}</p>
            <p><strong>For Property:</strong> {{ $house->address ?? 'N/A' }}</p> <!-- Assuming you might also pass the house -->
            <p><strong>Payment Method:</strong> {{ $payment->payment_method ?? 'N/A' }}</p>
            <p><strong>Reference:</strong> {{ $payment->reference ?? 'N/A' }}</p>
        </div>

        <div class="amount">
            Amount Received: ${{ number_format($payment->amount, 2) }}
        </div>

        <div class="footer">
            <p>Thank you for your payment!</p>
            <p>This is an automated receipt. No signature is required.</p>
        </div>
    </div>
</body>
</html>