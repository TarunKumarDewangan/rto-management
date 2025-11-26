<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vltd extends Model
{
    use HasFactory;
    protected $fillable = ['vehicle_id', 'vendor_name', 'actual_amount', 'bill_amount', 'valid_from', 'valid_until'];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
