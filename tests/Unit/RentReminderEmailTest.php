<?php

namespace Tests\Feature;

use App\Mail\RentReminderEmail;
use App\Models\RentPayment;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RentReminderEmailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test sending a rent reminder email.
     *
     * @return void
     */
    public function test_send_rent_reminder_email()
    {
        // Arrange: Create a tenant and a rent payment using factories
        $tenant = Tenant::factory()->create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);
        $rentPayment = RentPayment::factory()->create([
            'tenant_id' => $tenant->id,
            'amount' => 100.00,
            'due_date' => now()->addDays(7),
        ]);

        Mail::fake(); // Intercept emails

        // Act: Send the rent reminder email (this is where your logic goes)
        Mail::to($tenant->email)->send(new RentReminderEmail($tenant, $rentPayment));

        // Assert: Check if the email was sent and has the correct data
        Mail::assertSent(RentReminderEmail::class, function ($mail) use ($tenant, $rentPayment) {
            return $mail->hasTo($tenant->email) &&
                   $mail->tenant->id === $tenant->id &&
                   $mail->rentPayment->id === $rentPayment->id;
        });
    }
}
