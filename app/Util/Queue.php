<?php
namespace App\Util;
use App\Models\Car_type;
use App\Models\Queue_number;
use Carbon\Carbon;
class Queue {
    public static function generate($shop)
    {
        $queue_today = Queue_number::where('shop_id', '=', $shop->shop_id)->whereDate('created_at', Carbon::today())->orderBy('created_at', 'desc')->first();
        if($queue_today != null){
            $queue_number = $queue_today->number + 1;
            $queue['code'] = $shop->shop->key_code;
            $queue['number'] = $queue_number;
        }else{
            $queue['code'] = $shop->shop->key_code;
            $queue['number'] = 1;
        }
        return $queue;
    }
}