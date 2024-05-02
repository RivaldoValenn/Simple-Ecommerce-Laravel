<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::updated(function ($order) {
            // Cek apakah status order berubah menjadi 'canceled'
            if ($order->isDirty('status') && $order->status === 'canceled') {
                // Kembalikan jumlah stok produk dari setiap order item
                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;
                    $product->stock += $orderItem->qty; // Kembalikan stok
                    $product->save();
                }
            }
        });
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
