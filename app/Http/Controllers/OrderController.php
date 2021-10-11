<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Shop_user;
use App\Models\Queue_number;
use App\Models\Order_status;
use App\Models\Status;
use App\Util\ResponseJson;
use App\Util\OrderUtil;
use App\Util\Checker;
use App\Util\Queue;
use App\Util\StatusUtil;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(isset($_GET['id'])){
            $id = $_GET['id'];
            $data = array(
                'indonesia' => 'Pesanan Ditemukan',
                'english' => 'Order Founded',
                'data' => Order::find($id)->get(),
            );
            return response()->json(ResponseJson::response($data), 200);
        }else{
            return Datatables::of(Order::with('shop'))->make(true);
        }
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
        $check = Checker::valid($request, array('customer_name' => 'required', 'license_plate'=>'required', 'car_type_id'=>'required|numeric', 'product_id'=>'numeric', 'payment_method_id'=>'required|numeric', 'other_product_price'=>'numeric'));
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
    public function store(Request $request)
    {
        $user = Auth::user();
        $check = Checker::valid($request, array('customer_name' => 'required', 'license_plate'=>'required', 'car_type_id'=>'required|numeric', 'product_id'=>'numeric', 'other_product_price'=>'numeric'));
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
                $shop = Shop_user::with('shop')->where('user_id', $user->id)->get();
                $status = Status::where('shop_id', '=', $shop[0]->shop_id)->where('sort', '=', 1)->get();
                $order = new Order();
                $order->order_number = "CW-".time().rand(1000,9999);
                $order->customer_name = $request->customer_name;
                $order->license_plate = $request->license_plate;
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
                $order->shop_id = $shop[0]->shop_id;
                $order->save();
                $order_id = $order->id;
    
                $queue = Queue::generate($shop);
                $queue_number = new Queue_number();
                $queue_number->code = $queue['code'];
                $queue_number->number = $queue['number'];
                $queue_number->shop_id = $shop[0]->shop_id;
                $queue_number->order_id = $order_id;
                $queue_number->save();
    
                $order_status = new Order_status();
                $order_status->order_id = $order_id;
                $order_status->status_id = $status[0]->id;
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
            }
        }else{
            return response()->json(ResponseJson::response($check), 401);
        }
    }
    public function test()
    {
        $user = Auth::user();
        $shop = Shop_user::with('shop')->where('user_id', $user->id)->get();
        return Queue::generate($shop);
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
}
