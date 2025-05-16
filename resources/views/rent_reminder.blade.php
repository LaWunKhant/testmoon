<!-- resources/views/emails/rent_reminder.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rent Payment Reminder</title>
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
            background-color: #4a7aaf;
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
        .important {
            color: #d9534f;
            font-weight: bold;
        }
        .payment-details {
            background-color: #f9f9f9;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #4a7aaf;
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
        <h1>Rent Payment Reminder</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $tenantName }},</p>
        
        <p>This is a friendly reminder that your rent payment is due soon.</p>
        
        <div class="payment-details">
            <p><strong>Property Address:</strong> {{ $propertyAddress }}</p>
            <p><strong>Amount Due:</strong> ${{ number_format($amount, 2) }}</p>
            <p><strong>Due Date:</strong> <span class="important">{{ $dueDate }}</span></p>
        </div>
        
        <p>Please ensure your payment is made on or before the due date to avoid late fees. If you have already made this payment, please disregard this notice.</p>
        
        <p>If you have any questions or concerns regarding your payment, please don't hesitate to contact us.</p>
        
        <p>Thank you for your prompt attention to this matter.</p>
        
        <p>Best regards,<br>
        Property Management Team</p>
    </div>
    
    <div class="footer">
        <p>This is an automated reminder. Please do not reply to this email.</p>
    </div>
</body>
</html>