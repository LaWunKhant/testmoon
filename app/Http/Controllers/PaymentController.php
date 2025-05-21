<?php

namespace App\Http\Controllers;

use App\Models\RentPayment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * * Update the payment status of a rent payment.
     * *
     * * @param  int  $id  The ID of the RentPayment
     * * @return \Illuminate\Http\Response
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        $payment = RentPayment::findOrFail($id); // Find the payment or fail

        //  * Validate the request.  Ensure that the 'paid' value is provided
        $request->validate([
            'paid' => 'required|boolean',
        ]);

        $payment->paid = $request->input('paid');
        $payment->save();

        return response()->json(['message' => 'Payment status updated successfully', 'payment' => $payment]);
    }
}
