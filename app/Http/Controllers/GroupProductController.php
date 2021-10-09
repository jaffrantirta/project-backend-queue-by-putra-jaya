<?php

namespace App\Http\Controllers;

use App\Models\Group_product;
use App\Models\Shop_user;
use Illuminate\Http\Request;
use App\Util\ResponseJson;
use App\Util\Checker;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;

class GroupProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Datatables::of(Group_product::all())->make(true);
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
        $check = Checker::valid($request, array('name' => 'required'));
        if($check==null){
            $shop = Shop_user::with('shop')->where('user_id', $user->id)->get();
            $group_product = new Group_product();
            $group_product->name = $request->name;
            $group_product->description = $request->description;
            $group_product->shop_id = $shop[0]->shop_id;
            $group_product->save();

            $data = array(
                'indonesia' => 'Grup Dibuat',
                'english' => 'Group Created',
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
     * @param  \App\Models\Group_product  $group_product
     * @return \Illuminate\Http\Response
     */
    public function show(Group_product $group_product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Group_product  $group_product
     * @return \Illuminate\Http\Response
     */
    public function edit(Group_product $group_product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group_product  $group_product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $update = Group_product::find($id)->update($request->all()); 
        $data = array(
            'indonesia' => 'Grup Produk Telah Diperbaharui',
            'english' => 'Group Product Updated',
            'data' => null,
        );
        return response()->json(ResponseJson::response($data), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group_product  $group_product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete = Group_product::find($id)->delete(); 
        $data = array(
            'indonesia' => 'Grup Dihapus',
            'english' => 'Group Deleted',
            'data' => null,
        );
        return response()->json(ResponseJson::response($data), 200);
    }
}
