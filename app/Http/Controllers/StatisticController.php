<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Util\Statistic;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Order_status;
use App\Models\Shop_user;
use App\Models\Car_type;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatisticController extends Controller
{
    public function statistic()
    {
        $user = Auth::user();
        $shop_id = Shop_user::where('user_id', $user->id)->first()->shop_id;
        $data['statistic'] = array(
            'order_per_week' => $this->weekly($shop_id),
            'car_type' => $this->car_type_today($shop_id),
            'product' => $this->product_today($shop_id),
            'finish_order' => $this->success_order_today($shop_id)
        );
        return $data;

    }
    public function product_today($shop_id)
    {
        $products = Order::select(DB::raw('COUNT(product_id) as count'), 'product_id')
        ->where('shop_id', $shop_id)
        ->whereDate('created_at', Carbon::today())
        ->groupByRaw('product_id')
        ->with('product')
        ->get();
        $i = 0;
        $data['data'][] = 0;
        $data['lebels'][] = "";
        foreach($products as $x){
            $data['data'][$i] = $x->count;
            $data['lebels'][$i] = $x->product->name;
            $i++;
        }
        return $data;
    }
    public function car_type_today($shop_id)
    {
        $car_types = Car_type::where('shop_id', $shop_id)->get();
        $last_status_id = Status::where('shop_id', $shop_id)->orderBy('sort', 'desc')->first()->id;
        $data = null;
        $i = 0;
        if(count($car_types) > 0){
            foreach($car_types as $car_type){
                $data['data'][$i] = Order_status::join('orders', 'orders.id', '=', 'order_statuses.order_id')
                ->where('orders.car_type_id', $car_type->id)
                ->where('order_statuses.status_id', $last_status_id)
                ->where('order_statuses.is_active', true)
                ->whereDate('orders.created_at', Carbon::today())
                ->count();
                $data['lebels'][$i] = $car_type->name;
                $i++;
            }
            return $data;
        }else{
            $data['data'][0] = 0;
            $data['lebels'][0] = "";
            return $data;
        }
    }
    public function success_order_today($shop_id)
    {
        $last_status_id = Status::where('shop_id', $shop_id)->orderBy('sort', 'desc')->first()->id;
        return Order_status::join('orders', 'orders.id', '=', 'order_statuses.order_id')
        ->where('order_statuses.status_id', $last_status_id)
        ->where('order_statuses.is_active', true)
        ->whereDate('orders.created_at', Carbon::today())
        ->count();
    }
    public function weekly($shop_id)
    {
        $last_status_id = Status::where('shop_id', $shop_id)->orderBy('sort', 'desc')->first()->id;
        $dates = array(
            Carbon::now()->startOfWeek(),
            Carbon::now()->startOfWeek()->addDays(1),
            Carbon::now()->startOfWeek()->addDays(2),
            Carbon::now()->startOfWeek()->addDays(3),
            Carbon::now()->startOfWeek()->addDays(4),
            Carbon::now()->startOfWeek()->addDays(5),
            Carbon::now()->startOfWeek()->addDays(6),
        );
        $i = 0;
        foreach($dates as $x){
            $data['data'][] = Order_status::join('orders', 'orders.id', '=', 'order_statuses.order_id')
            ->where('status_id', $last_status_id)
            ->where('is_active', true)
            ->whereDate('orders.created_at', $x)
            ->count();
            $i++;
        }
        $data['lebels'] = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $data;
    }
}
