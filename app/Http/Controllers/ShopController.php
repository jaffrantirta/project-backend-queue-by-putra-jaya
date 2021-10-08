<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Shop;
use App\Models\Shop_user;
use App\Util\ResponseJson;
use Validator;
use App\Mail\Email;
use Illuminate\Support\Facades\Mail;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $validator = Validator::make($request->all(), [
            'shop_name' => 'required',
            'shop_address' => 'required',
            'shop_phone' => 'required|numeric|digits:12',
            'shop_email' => 'required|email',
            'user_role_id' => 'required',
            'user_name' => 'required',
            'email' => 'required|email|unique:users',
            'user_phone' => 'required|numeric|digits:12',
        ]);

        if ($validator->fails()) {
            $error_message="";
            $i=0;
            foreach($validator->errors()->all() as $error){
                $error_message = $error_message." ".$error;
                $i++;
            }
            $data = array(
                'status' => false,
                'indonesia' => $error_message,
                'english' => $error_message
            );
            return response()->json(ResponseJson::response($data), 401);            
        }
        DB::beginTransaction();
        try {
            $shop = new Shop();
            $shop->name = $request->shop_name;
            $shop->address = $request->shop_address;
            $shop->phone = $request->shop_phone;
            $shop->email = $request->shop_email;
            $shop->website = $request->shop_website;
            $shop->key_code = $request->shop_key_code;
            $shop->save();
            $shop_id = $shop->id;

            $password = rand(10000000,99999999);
            $user = new User();
            $user->role_id = $request->user_role_id;
            $user->name = $request->user_name;
            $user->email = $request->email;
            $user->phone = $request->user_phone;
            $user->password = bcrypt($password);
            $user->save();
            $user_id = $user->id;

            $shop_user = new Shop_user();
            $shop_user->user_id = $user_id;
            $shop_user->shop_id = $shop_id;
            $shop_user->save();

            $config = array(
                'user_name'=>$request->user_name,
                'email'=>$request->email,
                'password'=>$password,
            );
    
            $data = array(
                'title'=>'Registrasi Berhasil ',
                'opening'=>'Hai, '.$config['user_name'].' Terimakasi sudah melalukan registrasi silahkan login pada link berikut https://franweb.my.id dan gunakan kridensial berikut :',
                'content'=>'email : '.$config['email'].' password : '.$config['password'],
                'closing'=>'sebelum login mohon lakukan aktivasi terlebih dahulu dengan link berikut ',
                'closing_content'=>'https://franweb.my.id',
                'email'=>$config['email'],
                'name'=>$config['user_name']
            );
            Mail::send('email_template', ['mail' => $data], function ($m) use ($data) {
                $m->from('drivebali2016@gmail.com', 'POS');
                $m->to($data['email'], $data['name'])->subject('Registrasi Sukses');
            });
            DB::commit();
            $data = array(
                'indonesia' => 'Registrasi Berhasil, mohon untuk cek email anda untuk verifikasi akun',
                'english' => 'You are registered now, please chack your email to verify your account'
            );
            return response()->json(ResponseJson::response($data), 200);
        } catch (\Exception $e) {
            DB::rollback();
            $data = array(
                'status' => false,
                'indonesia' => 'Registrasi Gagal',
                'english' => 'Your Registration is Failed',
                'data' => array('error_message'=>$e)
            );
            return response()->json(ResponseJson::response($data), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function show(Shop $shop)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function edit(Shop $shop)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Shop $shop)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shop $shop)
    {
        //
    }
    public function send_email()
    {
        $config = array(
            'user_name'=>'Jaffran',
            'email'=>'franartika@gmail.com',
            'password'=>'9090909',
        );

        $data = array(
            'title'=>'Registrasi Berhasil ',
            'opening'=>'Hai, '.$config['user_name'].' Terimakasi sudah melalukan registrasi silahkan login pada link berikut https://franweb.my.id dan gunakan kridensial berikut :',
            'content'=>'email : '.$config['email'].' password : '.$config['password'],
            'closing'=>'sebelum login mohon lakukan aktivasi terlebih dahulu dengan link berikut ',
            'closing_content'=>'https://franweb.my.id',
            'email'=>$config['email'],
            'name'=>$config['user_name']
        );
        Mail::send('email_template', ['mail' => $data], function ($m) use ($data) {
            $m->from('drivebali2016@gmail.com', 'POS');
            $m->to($data['email'], $data['name'])->subject('Registrasi Sukses');
        });
        return "Email telah dikirim oke";
    }
}
