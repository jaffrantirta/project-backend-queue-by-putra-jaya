<?php

namespace App\Http\Controllers;

use App\Models\Verify_user;
use App\Models\Shop;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop_user;
use Illuminate\Support\Facades\Auth;
use App\Util\ResponseJson;
use App\Util\Checker;
use Hash;
use Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public $successStatus = 200;

    public function login(){
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = Auth::user();
            if($user->is_active){
                $user_detail = User::with('role')->find($user->id);
                $shop_detail = Shop_user::with('shop')->where('user_id', $user->id)->get();
                $data = array(
                    'indonesia' => 'Login Berhasil',
                    'english' => 'You are logged in',
                    'data' => array(
                        'user' => $user_detail,
                        'shop_user' => $shop_detail,
                        'token' => $user->createToken('nApp')->accessToken
                    )
                );
                return response()->json(ResponseJson::response($data), 200);
            }else{
                $data = array(
                    'status' => false,
                    'indonesia' => 'Akun anda belum aktif, cek email dan aktivasi melalui link',
                    'english' => 'Your account is not active, check your email and verify by link',
                );
                return response()->json(ResponseJson::response($data), 401);
            }
        }
        else{
            $data = array(
                'status' => false,
                'indonesia' => 'Email atau Password Salah',
                'english' => 'Email or Password is Wrong'
            );
            return response()->json(ResponseJson::response($data), 401);
        }
    }

    public function logout(Request $request)
    {
        $logout = $request->user()->token()->revoke();

        if($logout){
            $data = array(
                'indonesia' => 'Logout Berhasil',
                'english' => 'You are logged out'
            );
            return response()->json(ResponseJson::response($data), 204);
        }
    }

    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }
    public function verify($token)
    {
    $verifyUser = Verify_user::where('token', $token)->first();
    if(isset($verifyUser) ){
        $user = $verifyUser->user;
        if(!$user->is_active) {
        $verifyUser->user->is_active = 1;
        $verifyUser->user->save();
        $status = "Your e-mail is verified. You can now login.";
        } else {
        $status = "Your e-mail is already verified. You can now login.";
        }
    } else {
        return "Sorry your email cannot be identified.";
    }
    return $status;
    }
    public function change_password(Request $request)
    {
        $user = Auth::user();
        $userPassword = $user->password;
        $check = Checker::valid($request, array(
            'old_password' => 'required',
            'new_password' => 'required|same:new_confirm_password|min:8',
            'new_confirm_password' => 'required',
        ));
        if($check==null){
            if (!Hash::check($request->old_password, $userPassword)) {
                $data = array(
                    'status' => false,
                    'indonesia' => 'Password Salah',
                    'english' => 'Password is Wrong'
                );
                return response()->json(ResponseJson::response($data), 401);
            }else{
                $new_password = bcrypt($request->new_password);
                User::find($user->id)->update(array('password'=>$new_password));
                $data = array(
                    'indonesia' => 'Password Telah Diperbahaui',
                    'english' => 'Password Updated',
                );
                return response()->json(ResponseJson::response($data), 200);
            }
        }else{
            return response()->json(ResponseJson::response($check), 401);
        }
    }
    public function profile_update(Request $request)
    {
        $user = Auth::user();
        DB::beginTransaction();
            try {
                User::find($user->id)->update(array(
                    'name'=>$request->user_name,
                    'email'=>$request->user_email,
                    'phone'=>$request->user_phone,
                )); 
                Shop::find(Shop_user::where('user_id', $user->id)->first()->shop_id)->update(array(
                    'name'=>$request->shop_name,
                    'email'=>$request->shop_email,
                    'phone'=>$request->shop_phone,
                    'address'=>$request->shop_address,
                    'website'=>$request->shop_website,
                    'key_code'=>$request->shop_key_code,
                )); 

                DB::commit();
                $data = array(
                    'indonesia' => 'Profil Telah Diperbahaui',
                    'english' => 'Profile Updated',
                );
                return response()->json(ResponseJson::response($data), 200);
            } catch (\Exception $e) {
                DB::rollback();
                $data = array(
                    'status' => false,
                    'indonesia' => 'Gagal Perbaharui Profil',
                    'english' => 'Failed to update profile',
                    'data' => array('error_message'=>$e)
                );
                return response()->json(ResponseJson::response($data), 500);
            } catch (\Throwable $e) {
                DB::rollback();
                return $e;
            }
    }
    public function index()
    {
        $user = Auth::user();
        $shop_id = Shop_user::where('user_id', $user->id)->first()->shop_id;
        if(isset($_GET['id'])){
            $id = $_GET['id'];
            $data = array(
                'indonesia' => 'Pengguna Ditemukan',
                'english' => 'User Founded',
                'data' => User::with('role')->find($id),
            );
            return response()->json(ResponseJson::response($data), 200);
        }else{
            return Shop_user::where('shop_id', $shop_id)
            ->select('users.*', 'roles.name as role')
            ->join('users', 'users.id', '=', 'shop_users.user_id')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->latest()
            ->paginate(5);
        }
    }
    public function update(Request $request, $id)
    {
        User::find($id)->update($request->all()); 
        $data = array(
            'indonesia' => 'Pengguna Telah Diperbahaui',
            'english' => 'User Updated',
        );
        return response()->json(ResponseJson::response($data), 200);
    }
    public function destroy($id)
    {
        User::find($id)->delete(); 
        $data = array(
            'indonesia' => 'Pengguna Telah Dihapus',
            'english' => 'User Deleted',
        );
        return response()->json(ResponseJson::response($data), 200);
    }
    public function store(Request $request)
    {
        $user = Auth::user();
        $shop_id = Shop_user::where('user_id', $user->id)->first()->shop_id;
        $check = Checker::valid($request, array(
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required|numeric|digits:12',
        ));
        if($check==null){
            DB::beginTransaction();
            try {
                $password = rand(10000000,99999999);
                $user = new User();
                $user->role_id = $request->role_id;
                $user->name = $request->name;
                $user->email = $request->email;
                $user->phone = $request->phone;
                $user->password = bcrypt($password);
                $user->save();
                $user_id = $user->id;

                $shop_user = new Shop_user();
                $shop_user->user_id = $user_id;
                $shop_user->shop_id = $shop_id;
                $shop_user->save();

                $token = sha1(time());
                $verify_user = new Verify_user();
                $verify_user->user_id = $user_id;
                $verify_user->token = $token;
                $verify_user->save();

                $config = array(
                    'user_name'=>$request->name,
                    'email'=>$request->email,
                    'password'=>$password,
                );
        
                $data = array(
                    'title'=>'Registrasi Berhasil ',
                    'opening'=>'Hai, '.$config['user_name'].' Terimakasi sudah melalukan registrasi silahkan login pada link berikut https://franweb.my.id dan gunakan kridensial berikut :',
                    'content'=>'email : '.$config['email'].' password : '.$config['password'],
                    'closing'=>'sebelum login mohon lakukan aktivasi terlebih dahulu dengan link berikut ',
                    'closing_content'=>url('u/verify', $token),
                    'email'=>$config['email'],
                    'name'=>$config['user_name']
                );
                Mail::send('email_template', ['mail' => $data], function ($m) use ($data) {
                    $m->from('drivebali2016@gmail.com', 'POS');
                    $m->to($data['email'], $data['name'])->subject('Registrasi Sukses');
                });
                DB::commit();
                $data = array(
                    'indonesia' => 'Registrasi Berhasil, mohon untuk cek email untuk verifikasi akun',
                    'english' => 'You are registered now, please chack email to verify account'
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
        }else{
            return response()->json(ResponseJson::response($check), 401);
        }
    }
}
