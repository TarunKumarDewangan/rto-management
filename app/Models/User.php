<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\NotificationSetting;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'whatsapp_key',
        'whatsapp_host',
        // Settings
        'days_tax',
        'days_insurance',
        'days_fitness',
        'days_permit',
        'days_pucc',
        'days_vltd',
        'days_speed',
        'days_ll',
        'days_dl' // <--- Separate fields
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function citizens()
    {
        return $this->hasMany(Citizen::class);
    }

    // 2. Indirect: User has many Vehicles (Through Citizens)
    public function vehicles()
    {
        return $this->hasManyThrough(Vehicle::class, Citizen::class);
    }

    // 3. Direct: User has many LL Entries (Licenses table)
    public function licenses()
    {
        return $this->hasMany(License::class);
    }

}
