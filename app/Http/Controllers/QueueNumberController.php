<?php

namespace App\Http\Controllers;

use App\Models\Queue_number;
use App\Models\Shop_user;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Util\ResponseJson;
use App\Util\StatusOrder;
use Carbon\Carbon;

class QueueNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $shop = Shop_user::with('shop')->where('user_id', $user->id)->first();
        if(isset($_GET['id'])){
            $id = $_GET['id'];
            $queue = Queue_number::with('order')->where('id', $id)->get();
            $data = array(
                'indonesia' => 'Antrian Ditemukan',
                'english' => 'Queue(s) Founded',
                'data' => $queue,
            );
            return response()->json(ResponseJson::response($data), 200);
        }else{
            return Queue_number::where('is_called', false)
                ->where('queue_numbers.shop_id', $shop->shop_id)
                ->whereDate('queue_numbers.created_at', Carbon::today())
                ->orderBy('queue_numbers.created_at', 'ASC')
                ->join('orders', 'orders.id', '=', 'queue_numbers.order_id')
                ->select('queue_numbers.*', 'orders.customer_name', 'orders.license_plate')
                ->paginate(5);
        }
    }
    public function call()
    {
        $user = Auth::user();
        $shop = Shop_user::with('shop')->where('user_id', $user->id)->first();

        $queue = Queue_number::whereDate('created_at', Carbon::today())
        ->where('is_called', '0')
        ->where('shop_id', $shop->shop_id)
        ->orderBy('created_at', 'ASC')
        ->first();

        DB::beginTransaction();
        try {
            StatusOrder::update($queue->order_id, StatusOrder::sort(2));
            Queue_number::where('id', $queue->id)->update(['is_called'=>true]);
            DB::commit();

            $data = array(
                'indonesia' => 'Antrian Dipanggil',
                'english' => 'Queue Calling',
                'data' => $queue,
            );
            return response()->json(ResponseJson::response($data), 200);
        } catch (\Exception $e) {
            DB::rollback();
            $data = array(
                'status' => false,
                'indonesia' => 'Gagal Panggil Antrian',
                'english' => 'Failed to Call Queue',
                'data' => array('error_message'=>$e)
            );
            return response()->json(ResponseJson::response($data), 500);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Queue_number  $queue_number
     * @return \Illuminate\Http\Response
     */
    public function show(Queue_number $queue_number)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Queue_number  $queue_number
     * @return \Illuminate\Http\Response
     */
    public function edit(Queue_number $queue_number)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Queue_number  $queue_number
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Queue_number $queue_number)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Queue_number  $queue_number
     * @return \Illuminate\Http\Response
     */
    public function destroy(Queue_number $queue_number)
    {
        //
    }
}
