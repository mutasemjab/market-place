<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
     protected $guarded = [];

       protected $casts = [
        'selling_price' => 'decimal:2',
        'tax' => 'decimal:2',
        'status' => 'boolean',
        'is_featured' => 'boolean',
        'is_favourite' => 'boolean',
        'best_selling' => 'boolean',
    ];

   // protected $hidden = ['name_en', 'name_ar', ];
    protected $appends = ['name','description'];


    public function getNameAttribute()
    {
        $locale = app()->getLocale();
        $attribute = "name_{$locale}";
        return $this->{$attribute};
    }

    public function getDescriptionAttribute()
    {
        $locale = app()->getLocale();
        $attribute = "description_{$locale}";
        return $this->{$attribute};
    }


    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function photos()
    {
        return $this->hasMany(ProductPhoto::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

     public function offers()
    {
        return $this->hasMany(Offer::class)->whereDate('expired_at', '>', now());
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_products')->withPivot('variation_id','quantity','unit_price','total_price_after_tax','tax_percentage','tax_value','total_price_before_tax','discount_percentage','discount_value');
    }
    
     public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
