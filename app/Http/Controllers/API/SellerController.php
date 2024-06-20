<?php

namespace App\Http\Controllers\API;

use App\Message;
use App\ProductInquiry;
use App\ProductLikeLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\product;
use App\Sellers;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class SellerController extends Controller
{

    public function __construct()
    {
    }

    public function get_seller_products()
    {
        $user_id = auth()->user()->id;
        $seller = product::where('user_id', $user_id)->paginate(15);

        return response()->json(['result' => true, 'data' => $seller]);
    }
    //return popular product to the sellers api
    public function get_seller_products_by_popular()
    {
        $user_id = auth()->user()->id;
        $productlike = ProductLikeLog::where('user_id', $user_id)->paginate(15);

        foreach ($productlike as $productlikes) {
            $seller = product::where('user_id', $productlike->user_id)
                                ->groupBy('view_count')
                                ->orderBy(DB::raw('count(view_count)'), 'desc')
                                ->paginate(15);

            return response()->json(['result' => true, 'data' => $seller]);
        }
    }
    public function get_seller_dashboard_Statistics()
    {


        $user_id = auth()->user()->id;
        $approved = product::where('is_approved', 1)
            ->where('user_id', $user_id)
            ->count();
        $pending = product::where('is_approved', 0)
            ->where('user_id', $user_id)
            ->count();
        $rejected = product::where('is_approved', 2)
            ->where('user_id', $user_id)
            ->count();
        $closed = product::where('is_approved', 3)
            ->where('user_id', $user_id)
            ->count();
        $saved = product::where('is_approved', 4)
            ->where('user_id', $user_id)
            ->count();
        $hidden = product::where('is_approved', 5)
            ->where('user_id', $user_id)
            ->count();

        return response()->json([
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'closed' => $closed,
            'saved' => $saved,
            'hidden' => $hidden,
        ]);
    }

    public function get_seller_prouducts()
    {


        $user_id = auth()->user()->id;
        $approved = product::where('is_approved', 1)
            ->where('user_id', $user_id)
            ->get();
        $pending = product::where('is_approved', 0)
            ->where('user_id', $user_id)
            ->get();
        $rejected = product::where('is_approved', 2)
            ->where('user_id', $user_id)
            ->get();
        $closed = product::where('is_approved', 3)
            ->where('user_id', $user_id)
            ->get();
        $saved = product::where('is_approved', 4)
            ->where('user_id', $user_id)
            ->get();
        $hidden = product::where('is_approved', 5)
            ->where('user_id', $user_id)
            ->get();

        return response()->json([
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'closed' => $closed,
            'saved' => $saved,
            'hidden' => $hidden,
        ]);
    }

    public function get_product_details($id)
    {

        $product = product::find($id);

        if ($product) {
            return response()->json(['status' => true, 'data' => $product]);
        }

        return response()->json(['status' => false, 'data' => []], 404);
    }



    public function  delete_product($id)
    {

        $product = product::find($id);

        if ($product) {
            $product->delete();
            return response()->json(['status' => true, 'message' => "product deleted"]);
        }

        return response()->json(['status' => false, 'message' => "product not found"], 404);
    }


    public  function toggle_product_availability(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required',
        ]);
        /**Check the validation fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $product = product::find($request->id);

        if ($product) {
            $product->is_approved = $request->status;
            $product->save();
            return response()->json(['result' => true, 'data' => $product]);
        }

        return response()->json(['result' => false, 'message' => 'Product not found'], 404);
    }


    public function getStoreDetails($id)
    {
        $seller = User::leftJoin('sellers', 'users.id', '=', 'sellers.seller_id')
            ->where('users.id', $id)
            ->select('sellers.*', 'users.*')
            ->first();

        if (!$seller) {
            return response()->json(['result' => false, 'message' => 'Store not found'], 404);
        }

        $seller->product = product::where('user_id', $id)->get();



        return response()->json(['result' => true, 'store' => $seller]);
    }

    public function get_top_suppliers(Request $request){
        DB::statement("SET SQL_MODE=''");
        //returns products supplied by farmer according to volume submitted
        $products = product::groupBy('user_id')->orderBy(DB::raw('count(user_id)'), 'desc')->get();

        foreach ($products as $product) {

        //returns seller
        $suppliers = Sellers::join('users', 'sellers.seller_id', '=', 'users.id')
            ->where('users.account_types', 'farmer')
            ->where('users.id', $product->user_id)
            ->select(
                'users.id',
                'sellers.b_name as full_name',
                'users.email',
                'users.created_at'
            )
            ->groupBy('users.id')
            ->orderBy('users.id', 'desc')
            ->paginate(20);
        }

        return response()->json(['status'=>true, 'seller'=>$suppliers]);

    }


    public function profile_notification_count(){
        $id=auth()->user()->id;
    
        $message_count=Message::where('receiver_id', $id)
        ->where('seen_at', null)->count();

        $inquiry_count=ProductInquiry::join('products', 'products.id', 'product_inquiries.product_id')
        ->where('products.user_id', $id)->count();

        return response()->json(['message'=>$message_count, 'inquiry'=>$inquiry_count]);


    }

    
}
