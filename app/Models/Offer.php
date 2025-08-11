<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;
    protected $guarded=[];

     protected $casts = [
        'start_at' => 'date',
        'expired_at' => 'date',
        'price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function isActive()
    {
        return $this->start_at <= now() && $this->expired_at >= now();
    }

    public function isExpired()
    {
        return $this->expired_at < now();
    }
}
