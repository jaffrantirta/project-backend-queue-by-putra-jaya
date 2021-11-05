<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Group_product;
use App\Models\Product;
use App\Models\Car_type;
use Illuminate\Http\Request;
use App\Models\Shop_user;
use App\Models\Queue_number;
use App\Models\Order_status;
use App\Models\Status;
use App\Util\ResponseJson;
use App\Util\OrderUtil;
use App\Util\Checker;
use App\Util\Queue;
use App\Util\StatusOrder;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(isset($_GET['status_id'])){
            $status_id = $_GET['status_id'];
        }else{
            $status_id = StatusOrder::sort(2);
        }
        if(isset($_GET['id'])){
            $id = $_GET['id'];
            $data['status'] = Order_status::where('order_id', $id)
            ->join('statuses', 'statuses.id', '=', 'order_statuses.status_id')
            ->select('order_statuses.*', 'statuses.name')
            ->latest()
            ->first();
            $data['order'] = Order::find($id)->first();
            $data = array(
                'indonesia' => 'Pesanan Ditemukan',
                'english' => 'Order Founded',
                'data' => $data,
            );
            return response()->json(ResponseJson::response($data), 200);
        }else{
            return Order_status::where('status_id', $status_id)
            ->where('is_active', true)
            ->join('queue_numbers', 'queue_numbers.order_id', '=', 'order_statuses.order_id')
            ->join('orders', 'orders.id', '=', 'order_statuses.order_id')
            ->join('statuses', 'statuses.id', '=', 'order_statuses.status_id')
            ->orderBy('order_statuses.created_at', 'DESC')
            ->select('order_statuses.*', 'queue_numbers.code', 'queue_numbers.number', 'orders.customer_name', 'orders.license_plate', 'orders.grand_total', 'statuses.name as status')
            ->paginate(5);
        }
    }
    public function today()
    {
        if(isset($_GET['status_id'])){
            $status_id = $_GET['status_id'];
        }else{
            $status_id = StatusOrder::sort(2);
        }
        return Order_status::join('queue_numbers', 'queue_numbers.order_id', '=', 'order_statuses.order_id')
            ->join('orders', 'orders.id', '=', 'order_statuses.order_id')
            ->join('statuses', 'statuses.id', '=', 'order_statuses.status_id')
            ->select('order_statuses.*', 'queue_numbers.code', 'queue_numbers.number', 'orders.customer_name', 'orders.license_plate', 'orders.grand_total', 'statuses.name as status')
            ->whereDate('order_statuses.created_at', Carbon::today())
            ->where('status_id', $status_id)
            ->where('is_active', true)
            ->latest()
            ->paginate(5);
    }
    public function yesterday()
    {
        if(isset($_GET['status_id'])){
            $status_id = $_GET['status_id'];
        }else{
            $status_id = StatusOrder::sort(2);
        }
        return Order_status::join('queue_numbers', 'queue_numbers.order_id', '=', 'order_statuses.order_id')
            ->join('orders', 'orders.id', '=', 'order_statuses.order_id')
            ->join('statuses', 'statuses.id', '=', 'order_statuses.status_id')
            ->orderBy('order_statuses.created_at', 'DESC')
            ->select('order_statuses.*', 'queue_numbers.code', 'queue_numbers.number', 'orders.customer_name', 'orders.license_plate', 'orders.grand_total', 'statuses.name as status')
            ->whereDate('order_statuses.created_at', Carbon::yesterday())
            ->where('status_id', $status_id)
            ->where('is_active', true)
            ->paginate(5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function check_price(Request $request)
    {
        $user = Auth::user();
        $check = Checker::valid($request, array('customer_name' => 'required', 'license_plate'=>'required', 'car_type_id'=>'required|numeric'));
        if($check==null){
            $count = OrderUtil::counter(array(
                'car_type_id'=>$request->car_type_id,
                'product_id'=>$request->product_id,
                'other_product_name'=>$request->other_product_name,
                'other_product_price'=>$request->other_product_price,
            ));

            $data = array(
                'indonesia' => 'Perhitungan Berhasil',
                'english' => 'Calculation Successful',
                'data' => $count,
            );
            return response()->json(ResponseJson::response($data), 200);
        }else{
            return response()->json(ResponseJson::response($check), 401);
        }
    }
    public function update_status(Request $request)
    {
        $count_status = Order_status::where('order_id', $request->order_id)->count();
        StatusOrder::update($request->order_id, StatusOrder::sort($count_status+1));
        $data = array(
            'indonesia' => 'Status di perbaharui',
            'english' => 'Status has been updated',
        );
        return response()->json(ResponseJson::response($data), 200);
    }
    public function store(Request $request)
    {
        $user = Auth::user();
        $shop = Shop_user::with('shop')->where('user_id', $user->id)->first();
        $check = Checker::valid($request, array('customer_name' => 'required', 'license_plate'=>'required', 'car_type_id'=>'required|numeric'));
        if($check==null){
            DB::beginTransaction();
            try {
                $count = OrderUtil::counter(array(
                    'customer_name'=>$request->customer_name,
                    'license_plate'=>$request->license_plate,
                    'car_type_id'=>$request->car_type_id,
                    'product_id'=>$request->product_id,
                    'other_product_name'=>$request->other_product_name,
                    'other_product_price'=>$request->other_product_price,
                ));
                
                $order = new Order();
                $order->order_number = "CW-".time().rand(1000,9999);
                $order->customer_name = ucwords($request->customer_name);
                $order->license_plate = strtoupper($request->license_plate);
                $order->car_type_id = $request->car_type_id;
                $order->product_id = $request->product_id;
                $order->car_type_name = $count['car_type']->name;
                $order->car_type_price = $count['car_type']->price;
                if(isset($request->product_id)){
                    $order->product_name = $count['product']->name;
                    $order->product_price = $count['product']->price;
                }
                $order->other_product_name = $request->other_product_name;
                $order->other_product_price = $request->other_product_price;
                $order->grand_total = $count['grand_total'];
                $order->shop_id = $shop->shop_id;
                $order->save();
                $order_id = $order->id;

                $queue = Queue::generate($shop);
                $queue_number = new Queue_number();
                $queue_number->code = $queue['code'];
                $queue_number->number = $queue['number'];
                $queue_number->shop_id = $shop->shop_id;
                $queue_number->order_id = $order_id;
                $queue_number->save();
    
                $status = StatusOrder::sort(1);
                $order_status = new Order_status();
                $order_status->order_id = $order_id;
                $order_status->status_id = $status;
                $order_status->save();

                $print = array(
                    'queue'=>$queue_number,
                    'shop'=>$shop,
                    'order'=>$order
                );

                DB::commit();
                $data = array(
                    'indonesia' => 'Pesanan Dibuat',
                    'english' => 'Order Created',
                    'data' => $print,
                );
                return response()->json(ResponseJson::response($data), 200);
            } catch (\Exception $e) {
                DB::rollback();
                $data = array(
                    'status' => false,
                    'indonesia' => 'Pesanan Gagal Dibuat',
                    'english' => 'Your Order Failed',
                    'data' => array('error_message'=>$e)
                );
                return response()->json(ResponseJson::response($data), 500);
            } catch (\Throwable $e) {
                DB::rollback();
                return $e;
            }
        }else{
            return response()->json(ResponseJson::response($check), 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }
    public function order_preload()
    {
        $user = Auth::user();
        $shop = Shop_user::with('shop')->where('user_id', $user->id)->first();
        $groups = Group_product::where('shop_id', $shop->shop_id)->get();
        $i = 0;
        foreach($groups as $x){
            $gp[$i] = $x;
            $gp[$i]['products'] = Product::where('group_product_id', $x->id)->get();
            $i++;
        }
        $result['group_products'] = $gp;
        $result['car_types'] = Car_type::where('shop_id', $shop->shop_id)->get();
        $data = array(
            'indonesia' => 'Semua Produk Berdasarkan Grup',
            'english' => 'All Products Order by Group',
            'data' => $result,
        );
        return response()->json(ResponseJson::response($data), 200);
    }
}
