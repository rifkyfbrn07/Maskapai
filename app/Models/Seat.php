<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['airplane_id', 'seat_number', 'class'])]
class Seat extends Model
{
    public function airplane()
    {
        return $this->belongsTo(Airplane::class);
    }
}
