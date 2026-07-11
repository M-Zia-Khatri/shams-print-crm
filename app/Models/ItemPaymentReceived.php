<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPaymentReceived extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'party_name',
        'received_amount',
    ];
}
