<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\RequestProduct;
use App\ProductRequest;
use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

class ProductRequestController extends Controller
{
    public function save_product_request(RequestProduct $request)
    {
        return $request->save();
    }


    public function product_request_details($id)
    {


        $product_request = ProductRequest::find($id);

        if (!$product_request) {
            return response()->json(['status' => false, 'message' => 'Result not found'], 404);
        }

        return response()->json(['status' => true, 'details' => $product_request]);
    }

    public function get_product_request_list_seller()
    {

        $user = auth()->user();

        $product_chat_list = ProductRequest::join('products', 'product_requests.category_id', '=', 'products.category_id')

            ->join('users', 'users.id', '=', 'products.user_id')

            ->where('products.user_id', $user->id)

            ->groupBy('products.category_id', 'product_requests.name', 'product_requests.picture')

           ->select('product_requests.category_id', 'product_requests.name', 'product_requests.picture')
            ->paginate(20);

        return response()->json(['status' => true, 'list' => $product_chat_list]);
    }


    public function get_product_request_list_buyer()
    {

        $user = auth()->user();


        $product_chat_list = ProductRequest::where('product_requests.user_id', $user->id)

            ->paginate(20);

        return response()->json(['status' => true, 'list' => $product_chat_list]);
    }
}
