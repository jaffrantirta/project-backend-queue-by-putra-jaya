<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    public function group_product()
    {
        return $this->hasOne(Group_product::class, 'id', 'group_product_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class, 'product_id', 'id');
    }
    protected $fillable = [
        'name', 'group_product_id', 'price'
    ];
}
