<?php
namespace App\Util;
use App\Models\Car_type;
use App\Models\Queue_number;
use Carbon\Carbon;
class Queue {
    public static function generate($shop)
    {
        $queue_today = Queue_number::where('shop_id', '=', $shop[0]->shop_id)->whereDate('created_at', Carbon::today())->orderBy('number', 'desc')->get();
        if(count($queue_today) > 0){
            $queue_number = $queue_today[0]->number + 1;
            $queue = $shop[0]['shop']->key_code.$queue_number;
        }else{
            $queue = $shop[0]['shop']->key_code.'1';
        }
        return $queue;
    }
}