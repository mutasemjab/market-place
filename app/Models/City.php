<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    protected $fillable = [
        'name_en',
        'name_ar'
    ];

    /**
     * The categories that belong to the city.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'shop_category_cities', 'city_id', 'shop_category_id')
                    ->withTimestamps();
    }

}
