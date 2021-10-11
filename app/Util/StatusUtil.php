<?php
namespace App\Util;
use App\Models\Order_status;
use App\Models\Queue_number;
use App\Models\Status;
use Carbon\Carbon;
class StatusUtil {
    public static function update($order_id, $status_id)
    {
        $save = new Order_status();
        $save->order_id = $order_id;
        $save->status_id = $status_id;
        $save->save();
        return true;
    }
    public static function sort($shop_id, $sort)
    {
        $status = Status::where('shop_id', $shop_id)->where('sort', $sort)->get();
        return $status[0]->id;
    }
}