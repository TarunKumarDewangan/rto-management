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
        'speed_governor_id',
        'amount',
        'payment_date',
        'remarks'
    ];

    // Relationships
    public function tax() { return $this->belongsTo(Tax::class); }
    public function insurance() { return $this->belongsTo(Insurance::class); }
    public function pucc() { return $this->belongsTo(Pucc::class); }
    public function fitness() { return $this->belongsTo(Fitness::class); }
    public function vltd() { return $this->belongsTo(Vltd::class); }
    public function permit() { return $this->belongsTo(Permit::class); }
    public function speedGovernor() { return $this->belongsTo(SpeedGovernor::class); }

    // Helper to get parent vehicle easily if needed
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
}
