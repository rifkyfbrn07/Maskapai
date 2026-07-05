<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'iata_code', 'city', 'country'])]
class Airport extends Model
{
    public function departingFlights()
    {
        return $this->hasMany(Flight::class, 'departure_airport_id');
    }

    public function arrivingFlights()
    {
        return $this->hasMany(Flight::class, 'arrival_airport_id');
    }
}
