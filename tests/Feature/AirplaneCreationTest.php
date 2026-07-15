<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Airplane;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AirplaneCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_airplane_and_seats_without_manual_registration_number(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $airline = Airline::create([
            'name' => 'Garuda Indonesia',
            'code' => 'GA',
        ]);

        $response = $this->actingAs($admin)->post(route('airplanes.store'), [
            'airline_id' => $airline->id,
            'model' => 'Airbus A350',
            'capacity' => 12,
        ]);

        $response->assertRedirect(route('airplanes.index'));

        $airplane = Airplane::latest('id')->first();
        $this->assertNotNull($airplane);
        $this->assertNotEmpty($airplane->registration_number);
        $this->assertSame('Airbus A350', $airplane->model);
        $this->assertSame(12, $airplane->capacity);
        $this->assertDatabaseCount('seats', 12);
        $this->assertDatabaseHas('seats', [
            'airplane_id' => $airplane->id,
            'seat_number' => '1A',
        ]);
    }
}
