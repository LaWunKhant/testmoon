<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\User; // Import the User model
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_house(): void
    {
        // Arrange (Set up any necessary data)
        // Create a user
        $user = User::factory()->create();

        $houseData = House::factory()->raw(['owner_id' => $user->id]); // Use the user's ID

        // Act (Perform the action you want to test)
        $response = $this->post('/houses', $houseData);

        // Assert (Check if the result is what you expect)
        $response->assertStatus(201);
        $this->assertDatabaseHas('houses', ['address' => $houseData['address']]);
    }
}
