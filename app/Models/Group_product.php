<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group_product extends Model
{
    use HasFactory;
    public function product()
    {
        return $this->belongsTo(Product::class, 'group_product_id', 'id');
    }
    protected $fillable = [
        'name', 'description'
    ];
}
