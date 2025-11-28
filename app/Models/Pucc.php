<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pucc extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'pucc_number',
        'actual_amount',
        'bill_amount',
        'valid_from',
        'valid_until'
    ];

    // --- CRITICAL: Add this relationship ---
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
