<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class TenantControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Define the base path.
     *
     * @var string
     */
    protected $basePath = '/tenants'; //  Adjust this to match your routes/web.php

    public function test_index()
    {
        // Arrange: create a user, a house, and some tenants
        $user = User::factory()->create();
        $house = House::factory()->create(['owner_id' => $user->id]);
        $tenants = Tenant::factory()->count(3)->create(['house_id' => $house->id]);

        // Act
        $response = $this->get($this->basePath);

        // Assert
        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json) {
            $json->has(3)
                 ->each(fn (AssertableJson $json) => 
                     $json->whereType('id', 'integer')
                          ->whereType('name', 'string')
                          ->whereType('email', 'string')
                          ->whereType('phone', 'string|null')
                          ->whereType('house_id', 'integer')
                          ->whereType('rent', 'double')
                          ->etc()
                 );
        });
    }

    public function test_store_valid_data()
    {
        // Arrange
        $user = User::factory()->create();
        $house = House::factory()->create(['owner_id' => $user->id]);
        $tenantData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'house_id' => $house->id,
            'rent' => $this->faker->randomFloat(2, 100, 1000),
        ];

        // Act
        $response = $this->post($this->basePath, $tenantData);

        // Assert
        $response->assertStatus(201);
        $response->assertJson(function (AssertableJson $json) use ($tenantData) {
            $json->where('email', $tenantData['email'])
                 ->where('name', $tenantData['name'])
                 // Skip phone check as it might not be included in response
                 ->where('house_id', $tenantData['house_id'])
                 ->where('rent', $tenantData['rent'])
                 ->etc(); // Allow other fields to be present
        });

        $this->assertDatabaseHas('tenants', ['email' => $tenantData['email']]);
    }

    public function test_store_invalid_data()
    {
        // Arrange
        $invalidData = []; // Missing required fields

        // Act
        $response = $this->post($this->basePath, $invalidData);

        // Assert
        $response->assertStatus(422); // Check for a validation error
        $response->assertJson(function (AssertableJson $json) {
            $json->has('errors');
        });
    }

    public function test_show()
    {
        // Arrange
        $user = User::factory()->create();
        $house = House::factory()->create(['owner_id' => $user->id]);
        $tenant = Tenant::factory()->create(['house_id' => $house->id]);

        // Act
        $response = $this->get("$this->basePath/{$tenant->id}");

        // Assert
        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json) use ($tenant) {
            $json->where('id', $tenant->id)
                 ->where('email', $tenant->email)
                 ->etc();
        });
    }

    public function test_edit()
    {
        // Arrange
        $user = User::factory()->create();
        $house = House::factory()->create(['owner_id' => $user->id]);
        $tenant = Tenant::factory()->create(['house_id' => $house->id]);

        // Act
        $response = $this->get("$this->basePath/{$tenant->id}/edit");

        // Assert
        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json) use ($tenant) {
            $json->where('id', $tenant->id)
                 ->where('email', $tenant->email)
                 ->etc();
        });
    }

    public function test_update_valid_data()
    {
        // Arrange
        $user = User::factory()->create();
        $house = House::factory()->create(['owner_id' => $user->id]);
        $tenant = Tenant::factory()->create(['house_id' => $house->id]);
        $updatedData = [
            'name' => 'Updated Name',
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '1234567890',
            'house_id' => $house->id,
            'rent' => 999.99,
        ];

        // Act
        $response = $this->put("$this->basePath/{$tenant->id}", $updatedData);

        // Assert
        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json) use ($updatedData) {
            $json->where('name', $updatedData['name'])
                 ->where('email', $updatedData['email'])
                 ->etc();
        });
        $this->assertDatabaseHas('tenants', ['id' => $tenant->id, 'email' => $updatedData['email']]);
    }

    public function test_destroy()
    {
        // Arrange
        $user = User::factory()->create();
        $house = House::factory()->create(['owner_id' => $user->id]);
        $tenant = Tenant::factory()->create(['house_id' => $house->id]);

        // Act
        $response = $this->delete("$this->basePath/{$tenant->id}");

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
    }
}
