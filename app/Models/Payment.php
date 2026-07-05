<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['booking_id', 'payment_method', 'payment_status', 'transaction_code', 'amount'])]
class Payment extends Model
{
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
