<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Airplane;
use App\Models\Airport;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlightCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_flight_with_airline_id_inferred_from_airplane(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $airline = Airline::create([
            'name' => 'Lion Air',
            'code' => 'JT',
        ]);

        $airplane = Airplane::create([
            'airline_id' => $airline->id,
            'model' => 'Boeing 737',
            'registration_number' => 'PK-LION-1001',
            'capacity' => 180,
        ]);

        $departureAirport = Airport::create([
            'name' => 'Soekarno-Hatta',
            'iata_code' => 'CGK',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
        ]);

        $arrivalAirport = Airport::create([
            'name' => 'Ngurah Rai',
            'iata_code' => 'DPS',
            'city' => 'Denpasar',
            'country' => 'Indonesia',
        ]);

        $response = $this->actingAs($admin)->post(route('flights.store'), [
            'airplane_id' => $airplane->id,
            'flight_number' => 'JT-101',
            'departure_airport_id' => $departureAirport->id,
            'arrival_airport_id' => $arrivalAirport->id,
            'departure_time' => now()->addDay()->format('Y-m-d H:i:s'),
            'arrival_time' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
            'price' => 3500000,
        ]);

        $response->assertRedirect(route('flights.index'));

        $flight = Flight::latest('id')->first();
        $this->assertNotNull($flight);
        $this->assertSame($airline->id, $flight->airline_id);
        $this->assertSame($airplane->id, $flight->airplane_id);
        $this->assertSame($airplane->capacity, $flight->available_seats);
    }
}
