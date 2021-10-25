<?php

namespace App\Http\Controllers;

use App\Models\Verify_user;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop_user;
use Illuminate\Support\Facades\Auth;
use App\Util\ResponseJson;
use Validator;

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
            return response()->json([
                'message' => 'Successfully logged out'
            ]);
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
        return redirect('/login')->with('warning', "Sorry your email cannot be identified.");
    }
    return redirect('/login')->with('status', $status);
    }
}
