<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop_user;
use Illuminate\Http\Request;
use App\Util\ResponseJson;
use App\Util\Checker;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
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
            $data = array(
                'indonesia' => 'Produk Ditemukan',
                'english' => 'Product Founded',
                'data' => Product::where('id', $id)->with('group_product')->first(),
            );
            return response()->json(ResponseJson::response($data), 200);
        }else if(isset($_GET['group'])){
            $id = $_GET['group'];
            $data = array(
                'indonesia' => 'Produk Ditemukan',
                'english' => 'Product Founded',
                'data' => Product::where('group_product_id', $id)->with('group_product')->get(),
            );
            return response()->json(ResponseJson::response($data), 200);
        }else{
            return Product::join('group_products', 'group_products.id', '=', 'products.group_product_id')->select('products.*')
            ->where('group_products.shop_id', $shop->shop_id)
            ->orderByDesc('products.created_at')
            ->with('group_product')
            ->paginate(5);
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
        $user = Auth::user();
        $check = Checker::valid($request, array('name' => 'required', 'group_product_id'=>'required|numeric', 'price'=>'required|numeric'));
        if($check==null){
            $product = new Product();
            $product->name = $request->name;
            $product->group_product_id = $request->group_product_id;
            $product->price = $request->price;
            $product->save();

            $data = array(
                'indonesia' => 'Produk Ditambahkan',
                'english' => 'Product Added',
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
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $update = Product::find($id)->update($request->all()); 
        $data = array(
            'indonesia' => 'Produk Berhasil Diperbaharui',
            'english' => 'Product Updated',
            'data' => null,
        );
        return response()->json(ResponseJson::response($data), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete = Product::find($id)->delete(); 
        $data = array(
            'indonesia' => 'Produk Dihapus',
            'english' => 'Product Deleted',
            'data' => null,
        );
        return response()->json(ResponseJson::response($data), 200);
    }
}
