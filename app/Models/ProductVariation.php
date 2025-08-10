<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use HasFactory;
     protected $guarded = [];
    
     protected $casts = [
        'price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'cart_variations', 'variation_id', 'cart_id');
    }
}
