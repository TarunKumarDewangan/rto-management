<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'tax_id',
        'insurance_id',
        'pucc_id',
        'fitness_id',
        'vltd_id',
        'permit_id',
        'speed_governor_id', // Added
        'amount',
        'payment_date',
        'remarks'
    ];
}
