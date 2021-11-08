<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    public function shop()
    {
        return $this->hasOne(Shop::class, 'id', 'shop_id');
    }
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
    public function queue_number()
    {
        return $this->belongsTo(Queue_number::class, 'order', 'id');
    }
    public function order_status()
    {
        return $this->belongsTo(Order_status::class, 'order_id', 'id');
    }
}
