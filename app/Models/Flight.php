<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['airline_id', 'airplane_id', 'flight_number', 'departure_airport_id', 'arrival_airport_id', 'departure_time', 'arrival_time', 'price', 'available_seats'])]
class Flight extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $flight): void {
            if (empty($flight->airline_id) && !empty($flight->airplane_id)) {
                $airplane = Airplane::find($flight->airplane_id);
                if ($airplane) {
                    $flight->airline_id = $airplane->airline_id;
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'departure_time' => 'datetime',
            'arrival_time' => 'datetime',
            'price' => 'decimal:2',
            'available_seats' => 'integer',
        ];
    }

    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    public function airplane()
    {
        return $this->belongsTo(Airplane::class);
    }

    public function departureAirport()
    {
        return $this->belongsTo(Airport::class, 'departure_airport_id');
    }

    public function arrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'arrival_airport_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function isCompleted(): bool
    {
        if (!$this->arrival_time) {
            return false;
        }

        return Carbon::parse($this->arrival_time)->isPast();
    }

    public function statusLabel(): string
    {
        return $this->isCompleted() ? 'Completed' : 'Scheduled';
    }
}
