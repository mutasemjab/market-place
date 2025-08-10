<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopCategory extends Model
{
    use HasFactory;
    protected $guarded = [];

     public function shops()
    {
        return $this->hasMany(Shop::class,'category_id');
    }

       public function cities()
    {
        return $this->belongsToMany(City::class, 'shop_category_cities', 'shop_category_id', 'city_id')
                    ->withTimestamps();
    }


    /**
     * Get shops for this category in a specific city
     */
    public function shopsInCity($cityId)
    {
        return $this->shops()->where('city_id', $cityId);
    }
}
