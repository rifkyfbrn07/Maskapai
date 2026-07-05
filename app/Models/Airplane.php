<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['airline_id', 'model', 'registration_number', 'capacity'])]
class Airplane extends Model
{
    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function flights()
    {
        return $this->hasMany(Flight::class);
    }
}
