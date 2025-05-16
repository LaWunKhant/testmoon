    <html>
<head>
    <meta charset="utf-8">
    <title>Urgent: Overdue Rent Payment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #d9534f;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .warning {
            color: #d9534f;
            font-weight: bold;
        }
        .payment-details {
            background-color: #f9f9f9;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #d9534f;
        }
        .overdue-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .overdue-table th, .overdue-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .overdue-table th {
            background-color: #f2f2f2;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>URGENT: Overdue Rent Payment</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $tenantName }},</p>
        
        <p class="warning">Our records indicate that you have {{ $payments->count() }} overdue rent payment(s) for {{ $propertyAddress }}.</p>
        
        <p>The oldest payment is now <strong>{{ $daysOverdue }} days overdue</strong>. Please arrange for immediate payment to avoid additional late fees and potential legal action.</p>
        
        <div class="payment-details">
            <h3>Overdue Payment Details:</h3>
            
            <table class="overdue-table">
                <thead>
                    <tr>
                        <th>Due Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr>
                        <td>{{ $payment->due_date->format('M j, Y') }}</td>
                        <td>{{ $payment->description ?? 'Monthly Rent' }}</td>
                        <td>${{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2">Total Amount Due:</td>
                        <td>${{ number_format($totalDue, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <p>Please make your payment as soon as possible using one of the following methods:</p>
        <ul>
            <li>Online payment through our tenant portal</li>
            <li>Bank transfer to our account</li>
            <li>Check or money order delivered to our office</li>
        </ul>
        
        <p>If you are experiencing financial difficulties, please contact our office immediately to discuss possible payment arrangements.</p>
        
        <p>If you have already made this payment, please disregard this notice and provide us with the payment confirmation details.</p>
        
        <p>Thank you for your prompt attention to this matter.</p>
        
        <p>Sincerely,<br>
        Property Management Team</p>
    </div>
    
    <div class="footer">
        <p>This is an automated notice regarding your overdue payment. For assistance, please contact our office directly.</p>
    </div>
</body>
</html>