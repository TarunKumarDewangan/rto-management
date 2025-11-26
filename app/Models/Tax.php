<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'tax_mode',
        'govt_fee',
        'bill_amount',
        'type',
        'from_date',
        'upto_date'
    ];

    // --- ADD THIS FUNCTION ---
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    // ------------------------

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
