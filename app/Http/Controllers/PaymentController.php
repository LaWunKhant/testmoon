<?php

namespace App\Http\Controllers;

// Keep your existing imports
// Assuming you might use this later or it was from previous code
// Assuming from previous code
// From your earlier PaymentController code
use App\Mail\PaymentReceivedConfirmationMail;     // Your Payment model
use App\Models\House;     // Need Tenant model to get tenant email
use App\Models\Payment;      // Need House model for the form dropdown
use App\Models\Tenant;
use Illuminate\Http\Request; // Keep Log
use Illuminate\Support\Facades\Log; // Keep Validator
// *** Add the necessary imports for the Mailable and Mail facade ***
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    // Keep your existing methods like updatePaymentStatus, create, show...
    // (Assuming they are present in your actual file, even if not shown in the snippet)

    /**
     * Store a newly created payment record in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        // Optionally, fetch tenants and houses to populate dropdowns in the form
        // Assuming you have Tenant and House models
        $tenants = \App\Models\Tenant::all();
        $houses = \App\Models\House::all();

        return view('payments.create', compact('tenants', 'houses'));
    }

    public function store(Request $request)
    {
        // Keep your existing validation
        $validator = Validator::make($request->all(), [
            'house_id' => 'required|exists:houses,id',
            'tenant_id' => 'required|exists:tenants,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create the payment record
            $payment = Payment::create($validator->validated());

            // Fetch the tenant
            $tenant = Tenant::find($payment->tenant_id);

            // *** Fetch the house associated with the payment (if needed for PDF) ***
            $house = House::find($payment->house_id);

            // Ensure the tenant exists and has an email address
            if ($tenant && $tenant->email) {
                // Dispatch the Mailable to the tenant's email address
                // Pass the Payment, Tenant, and optional House objects to the Mailable
                Mail::to($tenant->email)->send(new PaymentReceivedConfirmationMail($payment, $tenant, $house)); // *** Pass $house here ***
                Log::info('Payment confirmation email sent to tenant: '.$tenant->email);
            } else {
                Log::warning('Could not send payment confirmation email: Tenant not found or tenant has no email address for payment ID: '.$payment->id);
            }

            return redirect()->route('payments.create')->with('success', 'Payment recorded successfully and confirmation email sent with PDF!'); // Updated success message

        } catch (\Exception $e) {
            Log::error('Failed to record payment or send confirmation email: '.$e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Failed to record payment or send confirmation email.');
        }
    }

    // Keep your existing methods like updatePaymentStatus, create, show...
}
