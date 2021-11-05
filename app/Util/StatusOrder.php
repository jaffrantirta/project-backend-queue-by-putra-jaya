<?php
namespace App\Util;
use App\Models\Order_status;
use App\Models\Queue_number;
use App\Models\Shop_user;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class StatusOrder {
    public static function update($order_id, $status_id)
    {
        Order_status::where('order_id', $order_id)->where('is_active', true)->oldest()->update(['is_active'=>false]);
        
        $save = new Order_status();
        $save->order_id = $order_id;
        $save->status_id = $status_id;
        $save->save();

        return true;
    }
    public static function sort($sort)
    {
        $user = Auth::user();
        $shop = Shop_user::with('shop')->where('user_id', $user->id)->first();
        $status = Status::where('shop_id', $shop->shop_id)->where('sort', $sort)->first();
        return $status->id;
    }
}