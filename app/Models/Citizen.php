<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Citizen extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'mobile_number',
        'email',
        'birth_date',
        'relation_type',
        'relation_name',
        'address',
        'state',
        'city_district',
    ];

    // --- ADD THIS FUNCTION ---
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // ------------------------

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
