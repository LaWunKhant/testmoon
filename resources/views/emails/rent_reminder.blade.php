<!DOCTYPE html>
   <html>
   <head>
       <title>Rent Reminder</title>
   </head>
   <body>
       Dear {{ $tenantName }}, <br>  {{-- Use the variable passed from the Mailable class --}}
        <p>This is a friendly reminder that your rent payment is due.</p>
        <p>Amount Due: ${{ $amountDue }}</p>
        <p>Due Date: {{ $dueDate }}</p>
        <p>Please make your payment as soon as possible.</p>
        <p>Thank you!</p>
   </body>
   </html>