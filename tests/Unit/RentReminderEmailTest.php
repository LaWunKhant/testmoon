<?php

namespace Tests\Feature;

use App\Mail\RentReminderMail;
use App\Models\RentPayment;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RentReminderEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_rent_reminder_email()
    {
        // Arrange: Create a tenant and a rent payment
        Mail::fake();

        // Create a user
        $user = \App\Models\User::factory()->create();

        // Create a house associated with the user
        $house = \App\Models\House::factory()->create(['owner_id' => $user->id]);

        // Create a tenant associated with the house
        $tenant = Tenant::factory()->create(['house_id' => $house->id]);

        $rentPayment = RentPayment::factory()->create(['tenant_id' => $tenant->id]);

        // Act: Send the email
        Mail::to($tenant->email)->send(new RentReminderMail($tenant, $rentPayment));

        // Assert: Check that the email was sent with the correct data
        Mail::assertSent(RentReminderMail::class, function ($mail) use ($tenant) {
            return $mail->hasTo($tenant->email);
        });
    }
}
