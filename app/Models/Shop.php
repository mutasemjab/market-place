<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;
      
    protected $guarded = [];

     public function category()
    {
        return $this->belongsTo(ShopCategory::class);
    }
   
    public function city()
    {
        return $this->belongsTo(City::class);
    }


}
