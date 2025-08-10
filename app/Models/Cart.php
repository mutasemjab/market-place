<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

     protected $guarded=[];
    public  function user()
    {
       return $this->belongsTo(User::class);
    }
  
    public  function offer()
    {
       return $this->belongsTo(Offer::class);
    }

    public function product()
    {
       return $this->belongsTo(Product::class);
    }
    
    public function variations()
    {
        return $this->hasMany(CartVariation::class);
    }
    
     public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    
}
