<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $guarded=[];


    protected $casts = [
        'date' => 'datetime',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->withPivot('variation_id','unit_id','quantity','unit_price','total_price_after_tax','tax_percentage','tax_value','total_price_before_tax','discount_percentage','discount_value','line_discount_value','line_discount_percentage'); // You can store the quantity of each product in the pivot table
    }

     public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
