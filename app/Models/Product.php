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
    protected $fillable = [
        'name', 'group_product_id', 'price'
    ];
}
