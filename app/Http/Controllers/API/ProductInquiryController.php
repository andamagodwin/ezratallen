<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ProductInquiry;
use App\ProductInquiryReply;
use App\Sellers;
use Illuminate\Support\Facades\Validator;

class ProductInquiryController extends Controller
{
    //

    public function save_inquiries(Request $request)
    {
        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'description' => 'required',
            'quantity' => 'required',
            'price' => 'required',

        ]);



        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        //create the inquiries
        ProductInquiry::create([
            'product_id' => $request->product_id,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'contact' => $request->contact,
            'price' => $request->price,
            'user_id' => auth()->user()->id

        ]);
        

        return response()->json(['status' => true, 'message' => 'Inquiries created succesfully']);
    }


    public function get_inquiry_details($id)
    {
        $inquiry = ProductInquiry::with(['replies', 'user', 'product'])->find($id);
        if ($inquiry) {

            $product = $inquiry->product;
            if ($product) {

                $seller = Sellers::where('seller_id', $product->user_id)->first();

                $inquiry->seller = $seller;
            }

            return response()->json(['status' => true, 'inquiry' => $inquiry], 200);
        }

        return response()->json(['status' => false, 'inquiry' => []], 404);
    }

    public function save_inquiry_reply(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'product_inquiry_id' => 'required',
            'message' => 'required'
        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $reply = ProductInquiryReply::create([
            'product_inquiry_id' => $request->product_inquiry_id,
            'message' => $request->message,
            'sender_id' => auth()->user()->id
        ]);

        return response()->json(['status' => true, 'reply' => $reply]);
    }

    public function get_inquiry_lists_seller(Request $request)
    {

        $user = auth()->user();

        $inquiries = ProductInquiry::with('replies')
            ->join('products', 'products.id', 'product_inquiries.product_id')
            ->where('products.user_id', $user->id)
            ->paginate(20);

        return response()->json(['status' => true, 'inquiries' => $inquiries]);
    }

    public function get_inquiry_lists_buyer(Request $request)
    {
        $user = auth()->user();
        $inquiries = ProductInquiry::leftJoin('products', 'products.id', 'product_inquiries.product_id')

            ->leftJoin('users', 'users.id', 'product_inquiries.user_id')
            ->leftJoin('sellers', 'sellers.seller_id', 'product_inquiries.user_id')
            ->with('replies')
            //->join('products', 'products.id', 'product_inquiries.product_id')
            ->where('product_inquiries.user_id', $user->id)
            ->select(
                'products.product_title',
                'product_inquiries.price',
                'products.id as product_id',
                'sellers.manager_name',
                'products.picture',
                'sellers.b_name',
                'users.full_name as seller_name',
                'product_inquiries.user_id',
                'product_inquiries.created_at',
                'product_inquiries.description',
                'product_inquiries.quantity',
                 'product_inquiries.id',
                'products.sub_category_id',
                'products.created_at as product_create_date',
                'products.longitude',
                'products.latitude',
                'products.address',
                'products.available_quantity',
                'products.units',
                'products.view_count',
                'products.currency'
            )
            ->paginate(20);

        return response()->json([
            'status' => true,
            'user' => auth()->user(),
            'inquiries' => $inquiries
        ]);
    }
}
