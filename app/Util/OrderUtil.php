<?php
namespace App\Util;
use App\Models\Car_type;
use App\Models\Product;
class OrderUtil {
    public static function counter($x)
    {
        $car_type = Car_type::find($x['car_type_id'])->get()[0];
        $other_product_name = $x['other_product_name'];
        $other_product_price = $x['other_product_price'];
        if($x['product_id'] == null){
            $product = null;
            $grand_total = $car_type->price + $other_product_price;
        }else{
            $product = Product::find($x['product_id'])->get()[0];
            $grand_total = $car_type->price + $product->price + $other_product_price;
        }
        
        $result = array(
            'car_type' => $car_type,
            'product' => $product,
            'other_product' => array(
                'other_product_name'=>$other_product_name,
                'other_product_price'=>$other_product_price
            ),
            'grand_total'=>$grand_total
        );
        return $result;
    }
}