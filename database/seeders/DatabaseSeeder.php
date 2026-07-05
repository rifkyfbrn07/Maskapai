<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Airport;
use App\Models\Airline;
use App\Models\Airplane;
use App\Models\Seat;
use App\Models\Flight;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users (RBAC)
        $users = [
            [
                'name' => 'Admin Officer',
                'email' => 'admin@flight.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Ticketing Officer',
                'email' => 'staff@flight.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Ahmad Penumpang',
                'email' => 'customer@flight.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Top Level Manager',
                'email' => 'manager@flight.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $u) {
            User::create($u);
        }

        // 2. Seed Airports
        $airports = [
            [
                'name' => 'Soekarno-Hatta International Airport',
                'iata_code' => 'CGK',
                'city' => 'Jakarta',
                'country' => 'Indonesia',
            ],
            [
                'name' => 'Juanda International Airport',
                'iata_code' => 'SUB',
                'city' => 'Surabaya',
                'country' => 'Indonesia',
            ],
            [
                'name' => 'Ngurah Rai International Airport',
                'iata_code' => 'DPS',
                'city' => 'Bali',
                'country' => 'Indonesia',
            ],
            [
                'name' => 'Kualanamu International Airport',
                'iata_code' => 'KNO',
                'city' => 'Medan',
                'country' => 'Indonesia',
            ],
            [
                'name' => 'Yogyakarta International Airport',
                'iata_code' => 'YIA',
                'city' => 'Yogyakarta',
                'country' => 'Indonesia',
            ],
            [
                'name' => 'Sultan Aji Muhammad Sulaiman Sepinggan Airport',
                'iata_code' => 'BPN',
                'city' => 'Balikpapan',
                'country' => 'Indonesia',
            ],
        ];

        $airportIds = [];
        foreach ($airports as $ap) {
            $airportIds[$ap['iata_code']] = Airport::create($ap)->id;
        }

        // 3. Seed Airlines
        $airlines = [
            [
                'name' => 'Garuda Indonesia',
                'code' => 'GA',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/e/ee/Garuda_Indonesia_logo.svg',
                'photos' => json_encode(['https://images.unsplash.com/photo-1436491865332-7a61a109cc05']),
            ],
            [
                'name' => 'Citilink',
                'code' => 'QG',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/8/8b/Citilink_logo.svg',
                'photos' => json_encode(['https://images.unsplash.com/photo-1540962351504-03099e0a754b']),
            ],
            [
                'name' => 'Batik Air',
                'code' => 'ID',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/e/ef/Batik_Air_Logo.svg',
                'photos' => json_encode([]),
            ],
            [
                'name' => 'Lion Air',
                'code' => 'JT',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/d/d4/Lion_Air_logo.svg',
                'photos' => json_encode([]),
            ],
            [
                'name' => 'Super Air Jet',
                'code' => 'IU',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/4/4e/Super_Air_Jet_Logo.svg',
                'photos' => json_encode([]),
            ],
        ];

        $airlineModels = [];
        foreach ($airlines as $al) {
            $airlineModels[] = Airline::create($al);
        }

        // 4. Seed Airplanes & Seating Maps
        $seatLetters = ['A', 'B', 'C', 'D', 'E', 'F'];
        $airplanesData = [
            ['model' => 'Boeing 737-800', 'capacity' => 180, 'business_rows' => 5, 'total_rows' => 30],
            ['model' => 'Airbus A320-200', 'capacity' => 150, 'business_rows' => 4, 'total_rows' => 25],
        ];

        $airplanesList = [];
        foreach ($airlineModels as $airline) {
            foreach ($airplanesData as $index => $apData) {
                // Create Airplane
                $airplane = Airplane::create([
                    'airline_id' => $airline->id,
                    'model' => $airline->name . ' - ' . $apData['model'],
                    'registration_number' => 'PK-' . strtoupper(\Illuminate\Support\Str::random(5)),
                    'capacity' => $apData['capacity'],
                ]);
                $airplanesList[] = $airplane;

                // Create Seats for the Airplane
                $seats = [];
                for ($row = 1; $row <= $apData['total_rows']; $row++) {
                    $class = ($row <= $apData['business_rows']) ? 'business' : 'economy';
                    foreach ($seatLetters as $letter) {
                        $seats[] = [
                            'airplane_id' => $airplane->id,
                            'seat_number' => $row . $letter,
                            'class' => $class,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                // Batch insert seats to improve speed
                Seat::insert($seats);
            }
        }

        // 5. Seed Flights (schedule flights departing from tomorrow for 7 days)
        $routes = [
            ['from' => 'CGK', 'to' => 'DPS', 'base_price' => 1200000, 'duration_hours' => 2],
            ['from' => 'DPS', 'to' => 'CGK', 'base_price' => 1250000, 'duration_hours' => 2],
            ['from' => 'CGK', 'to' => 'SUB', 'base_price' => 850000, 'duration_hours' => 1.5],
            ['from' => 'SUB', 'to' => 'CGK', 'base_price' => 880000, 'duration_hours' => 1.5],
            ['from' => 'CGK', 'to' => 'KNO', 'base_price' => 1500000, 'duration_hours' => 2.5],
            ['from' => 'KNO', 'to' => 'CGK', 'base_price' => 1450000, 'duration_hours' => 2.5],
            ['from' => 'DPS', 'to' => 'SUB', 'base_price' => 650000, 'duration_hours' => 1],
            ['from' => 'SUB', 'to' => 'DPS', 'base_price' => 600000, 'duration_hours' => 1],
            ['from' => 'YIA', 'to' => 'CGK', 'base_price' => 750000, 'duration_hours' => 1.2],
            ['from' => 'CGK', 'to' => 'YIA', 'base_price' => 770000, 'duration_hours' => 1.2],
            ['from' => 'CGK', 'to' => 'BPN', 'base_price' => 1300000, 'duration_hours' => 2.2],
            ['from' => 'BPN', 'to' => 'CGK', 'base_price' => 1350000, 'duration_hours' => 2.2],
        ];

        $flightSchedules = [];
        $flightCounter = 101;

        // Generate flight schedule for next 7 days
        for ($day = 1; $day <= 7; $day++) {
            $date = Carbon::tomorrow()->addDays($day - 1);
            
            foreach ($routes as $route) {
                // Select a random airplane
                $airplane = $airplanesList[array_rand($airplanesList)];
                
                // Get the airline code
                $airlineCode = $airplane->airline->code;
                
                // Departure times
                $hours = [7, 10, 13, 16, 19];
                $hour = $hours[array_rand($hours)];
                
                $departureTime = Carbon::create($date->year, $date->month, $date->day, $hour, 0, 0);
                $arrivalTime = (clone $departureTime)->addMinutes($route['duration_hours'] * 60);
                
                // Flight number
                $flightNumber = $airlineCode . '-' . $flightCounter++;

                $flightSchedules[] = [
                    'airline_id' => $airplane->airline_id,
                    'airplane_id' => $airplane->id,
                    'flight_number' => $flightNumber,
                    'departure_airport_id' => $airportIds[$route['from']],
                    'arrival_airport_id' => $airportIds[$route['to']],
                    'departure_time' => $departureTime,
                    'arrival_time' => $arrivalTime,
                    'price' => $route['base_price'] + rand(-50000, 100000), // add slight price variance
                    'available_seats' => $airplane->capacity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        Flight::insert($flightSchedules);
    }
}
