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
use App\Util\StatusUtil;
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
        $shop = Shop_user::with('shop')->where('user_id', $user->id)->get();
        if(isset($_GET['id'])){
            $id = $_GET['id'];
            $queue = Queue_number::with('order')->where('id', $id)->get();
            $data = array(
                'indonesia' => 'Antrian Ditemukan',
                'english' => 'Queue(s) Founded',
                'data' => $queue,
            );
            return response()->json(ResponseJson::response($data), 200);
        }else if(isset($_GET['shop'])){
            $vShop = $_GET['shop'];
            if($vShop){
                $queue = Queue_number::where('is_called', false)->where('shop_id', $shop[0]->shop_id)->whereDate('created_at', Carbon::today())->orderBy('number', 'ASC')->get();
                $data = array(
                    'indonesia' => 'Antrian Ditemukan',
                    'english' => 'Queue(s) Founded',
                    'data' => $queue,
                );
                return response()->json(ResponseJson::response($data), 200);
            }else{
                $data = array(
                    'status' => false,
                    'indonesia' => 'Antrian Ditemukan',
                    'english' => 'Queue(s) Founded',
                );
                return response()->json(ResponseJson::response($data), 404);
            }
        }else{
            $queue = Queue_number::where('is_called', false)->whereDate('created_at', Carbon::today())->orderBy('number', 'ASC')->get();
            $data = array(
                'indonesia' => 'Antrian Ditemukan',
                'english' => 'Queue(s) Founded',
                'data' => $queue,
            );
            return response()->json(ResponseJson::response($data), 200);
        }
    }
    public function call()
    {
        $user = Auth::user();
        $shop = Shop_user::with('shop')->where('user_id', $user->id)->get();
        $status_id_first = StatusUtil::sort($shop[0]->shop_id, 1);
        $status_id_second = StatusUtil::sort($shop[0]->shop_id, 2);

        $queue = Queue_number::where('is_called', false)
        ->where('shop_id', $shop[0]->shop_id)
        ->whereDate('created_at', Carbon::today())
        ->orderBy('number', 'ASC')
        ->limit(1)
        ->get();

        DB::beginTransaction();
        try {
            StatusUtil::update($queue[0]->order_id, $status_id_second);
            DB::table('queue_numbers')->where('id', $queue[0]->id)->update(['is_called' => true]);

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
