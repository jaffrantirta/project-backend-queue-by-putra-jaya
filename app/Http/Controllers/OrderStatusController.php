<?php

namespace App\Http\Controllers;

use App\Models\Order_status;
use Illuminate\Http\Request;
use App\Util\StatusUtil;

class OrderStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
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
    public function store(Request $request)
    {
        $user = Auth::user();
        $check = Checker::valid($request, array('order_id' => 'required|numeric', 'status_id'=>'required|numeric'));
        if($check==null){
            $shop = Shop_user::with('shop')->where('user_id', $user->id)->get();
            $status_id = StatusUtil::sort($shop[0]->shop_id, 2);
            
            $order_status = new OrderStatus();
            $order_status->order_id = $request->order_id;
            $order_status->status_id = $status_id;
            $order_status->save();

            $data = array(
                'indonesia' => 'Status Pesanan Diperbaharui',
                'english' => 'Order Status Updated',
                'data' => null,
            );
            return response()->json(ResponseJson::response($data), 200);
        }else{
            return response()->json(ResponseJson::response($check), 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order_status  $order_status
     * @return \Illuminate\Http\Response
     */
    public function show(Order_status $order_status)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order_status  $order_status
     * @return \Illuminate\Http\Response
     */
    public function edit(Order_status $order_status)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order_status  $order_status
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order_status  $order_status
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order_status $order_status)
    {
        //
    }
}
