<?php

namespace App\Http\Controllers;

use App\Models\House; // Keep your existing import
use App\Models\Payment;     // Import the new Payment model
use App\Models\RentPayment;     // Import Tenant model for the form dropdown
use App\Models\Tenant;      // Import House model for the form dropdown
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // Import Validator

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

    /**
     * Show the form for creating a new payment record.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Optionally, fetch tenants and houses to populate dropdowns in the form
        $tenants = Tenant::all(); // Assuming you have a Tenant model
        $houses = House::all();   // Assuming you have a House model

        return view('payments.create', compact('tenants', 'houses'));
    }

    /**
     * Store a newly created payment record in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Basic validation for the new payment record
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|exists:tenants,id',
            'house_id' => 'required|exists:houses,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            // Redirect back with validation errors and old input
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create the new payment record using the Payment model
        Payment::create($validator->validated());

        // Redirect the user to a success page or back to the form with a success message
        return redirect()->route('payments.create')->with('success', 'Payment recorded successfully!');

        // You might redirect to an index page for payments if you create one later
        // return redirect()->route('payments.index')->with('success', 'Payment recorded successfully!');
    }
}
