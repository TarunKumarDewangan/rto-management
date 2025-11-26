<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'citizen_id',
        'registration_no',
        'type',
        'make_model',
        'chassis_no',
        'engine_no',
    ];

    // Parent Relationship
    public function citizen()
    {
        return $this->belongsTo(Citizen::class);
    }

    // --- ADD THESE RELATIONSHIPS ---

    public function taxes()
    {
        return $this->hasMany(Tax::class);
    }

    public function insurances()
    {
        return $this->hasMany(Insurance::class);
    }

    public function puccs()
    {
        return $this->hasMany(Pucc::class);
    }

    public function fitnesses()
    {
        // Laravel tries to find 'fitnesses' table, but if you named model 'Fitness',
        // standard plural might imply the table is 'fitnesses' (which we created).
        return $this->hasMany(Fitness::class);
    }

    public function vltds()
    {
        return $this->hasMany(Vltd::class);
    }

    public function permits()
    {
        return $this->hasMany(Permit::class);
    }

    public function speedGovernors()
    {
        return $this->hasMany(SpeedGovernor::class);
    }
}
