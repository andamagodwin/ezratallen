<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductRequestController extends Controller
{
   public function save_product_request(RequestProduct $request){
    return $request->save();
   }


   public function product_request_details($id){


    $product_request=ProductRequest::find($id);

    if(!$product_request){
        return response()->json(['status'=>false, 'message'=>'Result not found'], 404);
    }

    return response()->json(['status'=>true, 'details'=>$product_request]);

   }

   public function get_product_request_list(){

    $user=auth()->user();

    $product_chat_list=ProductRequest::join('products', 'product_requests.category_id', '=', 'products.category_id' )

    ->join('products', 'users.id', '=', 'products.user_id')

    ->where('products.user_id', $user->id)

    ->groupBy('products.category_id')

    ->select('product_requests.*', 'users.full_name', 'users.picture')
    ->paginate(20);

    return response()->json(['status'=>true, 'list'=>$product_chat_list]);
   }
}
