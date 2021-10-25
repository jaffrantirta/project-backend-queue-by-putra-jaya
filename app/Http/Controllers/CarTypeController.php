<?php

namespace App\Http\Controllers;

use App\Models\Car_type;
use App\Models\Shop_user;
use Illuminate\Http\Request;
use Validator;
use App\Util\ResponseJson;
use App\Util\Checker;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;

class CarTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Car_type::latest()->paginate(5);
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
        $check = Checker::valid($request, array('name' => 'required|string', 'price' => 'required|numeric'));
        if($check==null){
            $shop = Shop_user::with('shop')->where('user_id', $user->id)->get();
            $car_type = new Car_type();
            $car_type->name = $request->name;
            $car_type->price = $request->price;
            $car_type->description = $request->description;
            $car_type->shop_id = $shop[0]->shop_id;
            $car_type->save();

            $data = array(
                'indonesia' => 'Tipe Kendaraan Tersimpan',
                'english' => 'Car Type Saved',
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
     * @param  \App\Models\Car_type  $car_type
     * @return \Illuminate\Http\Response
     */
    public function show(Car_type $car_type)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Car_type  $car_type
     * @return \Illuminate\Http\Response
     */
    public function edit(Car_type $car_type)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Car_type  $car_type
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $car_type = Car_type::find($id)->update($request->all()); 
        $data = array(
            'indonesia' => 'Tipe Kendaraan Telah Diperbahaui',
            'english' => 'Car Type Updated',
            'data' => null,
        );
        return response()->json(ResponseJson::response($data), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Car_type  $car_type
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $car_type = Car_type::find($id)->delete(); 
        $data = array(
            'indonesia' => 'Tipe Kendaraan Telah Dihapus',
            'english' => 'Car Type Deleted',
            'data' => null,
        );
        return response()->json(ResponseJson::response($data), 200);
    }
}
