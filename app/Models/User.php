<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;



class User extends Authenticatable
{
   use HasApiTokens, HasFactory, Notifiable;

    protected $guarded=[];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'total_points' => 'decimal:2',
            'activate' => 'integer',
        ];
    }

     public function addresses()
    {
       return $this->hasMany(UserAddress::class);
    }

     public function orders()
    {
       return $this->hasMany(Order::class);
    }
    public function favourites()
    {
        return $this->belongsToMany(Product::class, 'favourites', 'user_id', 'product_id');
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_users','user_id', 'coupon_id');
    }

 
}