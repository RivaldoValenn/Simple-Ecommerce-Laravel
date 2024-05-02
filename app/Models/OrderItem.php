<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        // Event listener untuk mengurangi stok setelah order item dibuat
        static::created(function ($orderItem) {
            $product = $orderItem->product;
            $product->stock -= $orderItem->qty;
            $product->save();
        });
    }


    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
