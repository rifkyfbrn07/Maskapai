<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Airplane;
use App\Models\Airport;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlightCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_completed_badge_for_past_flight(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
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

        Flight::create([
            'airline_id' => $airline->id,
            'airplane_id' => $airplane->id,
            'flight_number' => 'JT-707',
            'departure_airport_id' => $departureAirport->id,
            'arrival_airport_id' => $arrivalAirport->id,
            'departure_time' => now()->subDay(),
            'arrival_time' => now()->subDay()->addHours(2),
            'price' => 3500000,
            'available_seats' => $airplane->capacity,
        ]);

        $response = $this->actingAs($admin)->get(route('flights.index'));

        $response->assertStatus(200);
        $response->assertSee('Completed');
    }

    public function test_customer_cannot_book_a_completed_flight(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $airline = Airline::create([
            'name' => 'Lion Air',
            'code' => 'JT',
        ]);

        $airplane = Airplane::create([
            'airline_id' => $airline->id,
            'model' => 'Boeing 737',
            'registration_number' => 'PK-LION-1002',
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

        $flight = Flight::create([
            'airline_id' => $airline->id,
            'airplane_id' => $airplane->id,
            'flight_number' => 'JT-808',
            'departure_airport_id' => $departureAirport->id,
            'arrival_airport_id' => $arrivalAirport->id,
            'departure_time' => now()->subDay(),
            'arrival_time' => now()->subDay()->addHours(2),
            'price' => 3500000,
            'available_seats' => $airplane->capacity,
        ]);

        $response = $this->actingAs($customer)->get(route('bookings.new', ['flight' => $flight->id, 'class' => 'economy']));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'This flight has already completed.');
    }
}
