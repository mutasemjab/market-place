<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCoupon extends Model
{
    use HasFactory;

     protected $guarded=[];
    
      // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the Coupon model
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
