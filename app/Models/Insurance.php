<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'company',
        'policy_number', // <--- ADD THIS LINE
        'type',
        'actual_amount',
        'bill_amount',
        'start_date',
        'end_date'
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
