<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. airports
        Schema::create('airports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('iata_code', 5)->unique();
            $table->string('city');
            $table->string('country');
            $table->timestamps();
        });

        // 2. airlines
        Schema::create('airlines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10)->unique();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->string('photos')->nullable();
            $table->timestamps();
        });

        // 3. airplanes
        Schema::create('airplanes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->constrained('airlines')->onDelete('cascade');
            $table->string('model');
            $table->string('registration_number');
            $table->integer('capacity');
            $table->text('description')->nullable();
            $table->string('photos')->nullable();
            $table->timestamps();
        });

        // 4. seats
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airplane_id')->constrained('airplanes')->onDelete('cascade');
            $table->string('seat_number');
            $table->enum('class', ['economy', 'business', 'first']);
            $table->timestamps();

            $table->unique(['airplane_id', 'seat_number']);
        });

        // 5. flights
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->constrained('airlines')->onDelete('cascade');
            $table->foreignId('airplane_id')->constrained('airplanes')->onDelete('cascade');
            $table->string('flight_number');
            $table->foreignId('departure_airport_id')->constrained('airports')->onDelete('cascade');
            $table->foreignId('arrival_airport_id')->constrained('airports')->onDelete('cascade');
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->decimal('price', 12, 2);
            $table->integer('available_seats');
            $table->timestamps();
        });

        // 6. bookings
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('flight_id')->constrained('flights')->onDelete('cascade');
            $table->string('booking_code')->unique(); // PNR
            $table->integer('total_passengers');
            $table->decimal('total_price', 12, 2);
            $table->enum('status', ['pending', 'waiting', 'confirmed', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        // 7. passengers
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->string('full_name');
            $table->enum('gender', ['male', 'female']);
            $table->date('birth_date');
            $table->string('passport_number'); // NIK or passport
            $table->string('seat_number');
            $table->timestamps();
        });

        // 8. payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->string('payment_method')->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('transaction_code')->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('passengers');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('flights');
        Schema::dropIfExists('seats');
        Schema::dropIfExists('airplanes');
        Schema::dropIfExists('airlines');
        Schema::dropIfExists('airports');
    }
};
