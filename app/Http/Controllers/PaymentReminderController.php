<?php

namespace App\Http\Controllers;

use App\Mail\OverduePaymentReminderMail;
use App\Models\RentPayment;
use App\Models\Tenant;
use Illuminate\Support\Facades\Mail;

class PaymentReminderController extends Controller
{
    /**
     * Send overdue payment reminders to tenants.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendOverdueReminders()
    {
        // Get tenants with overdue payments
        $tenantsWithOverduePayments = Tenant::whereHas('rentPayments', function ($query) {
            $query->where('due_date', '<', now())
                ->where('paid', 0); //  paid column is 0 (false)
        })->get();

        // Send an email to each tenant with overdue payments
        foreach ($tenantsWithOverduePayments as $tenant) {
            $overduePayments = RentPayment::where('tenant_id', $tenant->id)
                ->where('due_date', '<', now())
                ->where('paid', 0) //  paid column is 0 (false)
                ->get();

            if ($overduePayments->isNotEmpty()) {
                Mail::to($tenant->email)->send(new OverduePaymentReminderMail($tenant, $overduePayments));
            }
        }

        return response()->json(['message' => 'Overdue payment reminders sent!']);
    }
}
