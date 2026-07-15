<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Str;

#[Fillable(['airline_id', 'model', 'registration_number', 'capacity'])]
class Airplane extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $airplane): void {
            if (empty($airplane->registration_number)) {
                $airplane->registration_number = self::generateRegistrationNumber($airplane);
            }
        });
    }

    private static function generateRegistrationNumber(self $airplane): string
    {
        $modelPrefix = preg_replace('/[^A-Za-z0-9]/', '', $airplane->model ?? 'AIRCRAFT');
        $modelPrefix = strtoupper(substr($modelPrefix, 0, 3));
        $modelPrefix = $modelPrefix ?: 'AIR';

        $airlineCode = 'PK';
        if (!empty($airplane->airline_id)) {
            $airline = Airline::find($airplane->airline_id);
            if ($airline && !empty($airline->code)) {
                $airlineCode = strtoupper($airline->code);
            }
        }

        return sprintf('%s-%s-%s', $modelPrefix, $airlineCode, strtoupper(Str::random(4)));
    }

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
