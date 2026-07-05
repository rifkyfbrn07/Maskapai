<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'code', 'logo', 'photos', 'description'])]
class Airline extends Model
{
    public function airplanes()
    {
        return $this->hasMany(Airplane::class);
    }

    public function flights()
    {
        return $this->hasMany(Flight::class);
    }
}
