<?php

namespace App\Http\Controllers;

use App\Models\Payment_method;
use Illuminate\Http\Request;
use App\Models\Shop_user;
use App\Util\ResponseJson;
use App\Util\Checker;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Datatables::of(Payment_method::all())->make(true);
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
        $check = Checker::valid($request, array('name'=>'required', 'file' => 'required|mimes:jpg,jpeg,png|max:2048'));
        $shop = Shop_user::with('shop')->where('user_id', $user->id)->get();
        if($check==null){
            if ($file = $request->file('file')) {
                $path = 'files/images/payment_logo/';
                $name = time().$file->getClientOriginalName();
    
                $payment_method = new Payment_method();
                $payment_method->name = $request->name;
                $payment_method->logo= $path.$name;
                $payment_method->shop_id= $shop[0]->shop_id;
                $payment_method->save();
                $file->move($path,$name);
                    
                $data = array(
                    'indonesia' => 'Metode Pembayaran Ditambahkan',
                    'english' => 'Payment Method Added',
                    'data' => null,
                );
                return response()->json(ResponseJson::response($data), 200);
            }
        }else{
            return response()->json(ResponseJson::response($check), 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Payment_method  $payment_method
     * @return \Illuminate\Http\Response
     */
    public function show(Payment_method $payment_method)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Payment_method  $payment_method
     * @return \Illuminate\Http\Response
     */
    public function edit(Payment_method $payment_method)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payment_method  $payment_method
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $check = Checker::valid($request, array('name'=>'required', 'file' => 'required|mimes:jpg,jpeg,png|max:2048'));
        $shop = Shop_user::with('shop')->where('user_id', $user->id)->get();
        if($check==null){
            if ($file = $request->file('file')) {
                $update = Payment_method::find($id);
                $path = 'files/images/payment_logo/';
                $name = time().$file->getClientOriginalName();
                $path_remove = $update->logo;
    
                $update->update(array(
                    'name'=>$request->name,
                    'logo'=>$path.$name,
                    'shop_id'=>$shop[0]->shop_id
                ));
                $file->move($path,$name);
                // unlink(public_path($path_remove));
                $data = array(
                    'indonesia' => 'Metode Pembayaran Telah Diperbaharui',
                    'english' => 'Payment Method Updated',
                    'data' => null,
                );
                return response()->json(ResponseJson::response($data), 200);
            }
        }else{
            return response()->json(ResponseJson::response($check), 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payment_method  $payment_method
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete = Payment_method::find($id);
        $path = $delete->logo;
        $delete->delete();
        unlink(public_path($path));
        $data = array(
            'indonesia' => 'Metode Pembayaran Dihapus',
            'english' => 'Payment Method Deleted',
            'data' => null,
        );
        return response()->json(ResponseJson::response($data), 200);
    }
}
