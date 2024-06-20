<?php

namespace App\Http\Controllers\API;

use App\Events\SmsNotificationEvent;
use App\Helper\ProductHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\Save;
use App\Mail\OrderPlaced;
use App\Notification;
use App\product;
use App\ProductOrder;
use App\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{





    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function updateOrderView($id)
    {
        $product = ProductOrder::find($id);
        if ($product) {
            $product->view_status = '1';
            $product->save();

            return response()->json(['result' => true]);
        }
    }



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $id = auth()->user()->id;
        // get order for the logged in user 
        $my_orders = ProductOrder::join('products', 'products.id', '=', 'product_orders.product_id')
            ->where('customer_id', $id)
            ->select(
                'product_orders.created_at',
                'product_orders.order_status',
                'products.picture',
                'products.price',
                'products.currency',
                'products.id as product_id',
                'product_orders.id',
                'product_orders.phone',
                'products.units',
                'product_orders.customer_id',
                'product_orders.seller_id',
                'product_orders.qty',
                'product_orders.amount',
                'product_orders.view_status',
                'product_orders.name',
                'product_orders.address',
                'product_orders.instruction',
                'products.product_title',
                'products.user_id'
            )
            ->orderBy('product_orders.id', 'DESC')
            ->get();
        foreach ($my_orders as $c) {

            $user_details = User::find($c->customer_id);
            if ($user_details) {
                //$c->$buyer=$buyer;
                $c->buyer = $user_details;
            }
            $c->seller = User::find($c->seller_id);
        }


        //in coming orders to the logged in user 

        $in_orders = ProductOrder::join('products', 'products.id', '=', 'product_orders.product_id')
            ->where('products.user_id', $id)
            ->select(
                'product_orders.created_at',
                'product_orders.order_status',
                'products.picture',
                'products.price',
                'products.currency',
                'products.id as product_id',
                'product_orders.id',
                'product_orders.phone',
                'products.units',
                'product_orders.customer_id',
                'product_orders.qty',
                'product_orders.amount',
                'product_orders.view_status',
                'product_orders.name',
                'product_orders.address',
                'product_orders.instruction',
                'products.product_title',
                'products.user_id'
            )
            ->orderBy('product_orders.id', 'DESC')
            ->get();
        foreach ($in_orders as $c) {

            $user_details = User::find($c->customer_id);
            if ($user_details) {
                //$c->$buyer=$buyer;
                $c->buyer = $user_details;
            } else {
                $c->buyer = "perfect";
            }
        }
        $sells_count = ProductOrder::join('products', 'products.id', '=', 'product_orders.product_id')
            ->where('products.user_id', $id)->where('view_status', '0')->count();


        $purchase_count = Notification::where('user_id', $id)
            ->where('notification_type', '1')
            ->where('status', '0')->count();

        return response()->json([
            'sales' => $in_orders, 'purchase' => $my_orders,
            'purchase_count' => $purchase_count,
            'sells_count' => $sells_count
        ]);
    }


    /**
     * Cancel Order 
     *
     * @return \Illuminate\Http\Response
     */


    public function AcceptOrCancelOrder(Request $request)
    {

        $order = ProductOrder::find($request->id);

        $subject = "Your Farmsell Order $order->order_number for $order->qty $order->unit of $order->product_name";

        $object = (object) array(
            'subject' => $subject,

        );

        $notificationBody = ProductOrder::find($request->id);






        //get product
        $product = product::find($order->product_id);
        //get the buyer
        $buyer = User::find($order->customer_id);
        //get the seller
        $seller = User::find($product->user_id);



        //attach screen object

        //add in app notification  to the buyer
        $notificationBody->picture = $product->picture;

        $notificationBody->price = $product->price;
        $notificationBody->currency = $product->currency;
        $notificationBody->product_id = $product->id;
        $notificationBody->units = $product->units;
        $notificationBody->buyer = $buyer;
        $notificationBody->seller = $seller;

        $notication = new ProductHelper();


        if ($order) {


            $order->order_status = $request->order_status;

            $order->declined_by = $request->declined_by == 1 ? '1' : 0;


            if ($request->order_status == 1) {

                if ($product) {
                    $product->available_quantity = ($product->available_quantity - $order->qty);
                    $product->save();
                }
            }


            $arr_seller = explode(' ', trim($seller->full_name));

            $arr_buyer = explode(' ', trim($buyer->full_name));

            //check if its approved


            if ($request->order_status == 1) {

                //send email to buyer about the approval

                $body = <<<EOD
                  Here is great news for you. $seller->full_name has accepted your order $order->order_number for $order->qty $order->unit of $order->product_name from Farmsell marketplace. Please login into your Farmsell account to engage $arr_seller[0]; seller immediately to finalize the arrangements for this order. Should you have any further queries or need help, please contact us.  
                 EOD;

                $object->subject = $subject;
                $object->name = $arr_buyer[0];
                $object->body = $body;
                $email_object = new OrderPlaced($object);

                if ($buyer->email) {

                    Mail::to(strtolower($buyer->email))->send($email_object);
                }
                //send an sms notification to buyer
                $message = <<<EOD
                Hi $buyer->full_name, Your order for $order->qty $order->unit of $order->product_name  has been accepted by  $arr_seller[0].  Please login into your Farmsell account https://farmsell.org/SignIn to engage $arr_seller[0] immediately to finalize the arrangements for this purchase.  Regards, Farmsell 
                EOD;


                $notif = "Your order for $order->qty $order->unit of $order->product_name  has been accepted by  $arr_seller[0].";


                $notication->NotificationHelper($buyer->id, $notif, $notif, 'OrderDetails', json_encode($notificationBody), false, $product->id);


                try {

                    if ($buyer->phone)
                        event(new SmsNotificationEvent($buyer->country_code . ltrim($buyer->phone, '0'), $message));
                } catch (Exception $e) {
                    Log::info($e);
                }
            }

            //check if it's declined or approved


            if ($request->order_status == 2) {

                if (!$request->declined_by) {
                    //send an email to the seller about the cancel

                    $body = <<<EOD
                    Greetings from Farmsell. The buyer: $buyer->full_name had placed an order $order->order_number for $order->qty $order->unit of $order->product_name from you. Apparently $arr_buyer[0] has just cancelled the order for loss of interest or other reasons. Please login into your Farmsell account to view any details or follow-up actions. Should you have any further queries or need help, please contact us.  
                   EOD;
                    $object->subject = $subject;
                    $object->name = $arr_seller[0];
                    $object->body = $body;
                    $email_object = new OrderPlaced($object);


                    $notif = "Buyer $buyer->full_name has canceled the order for $order->qty $order->unit of $order->product_name";


                    $notication->NotificationHelper($seller->id, $notif, $notif, 'OrderDetails', json_encode($notificationBody), $buyer->id, $product->id);


                    if ($seller->email) {
                        Mail::to(strtolower($seller->email))->send($email_object);
                    }
                } else {
                    //send an email to buyer about the cancel
                    $body = <<<EOD
                    Thanks for taking time to buy with Farmsell. Your order $order->order_number for $order->qty $order->unit of $order->product_name was successfully received by $arr_seller[0] from Farmsell marketplace. Whereas $arr_seller[0] feel excited about your order, this seller is currently unable to supply the order  $order->qty $order->unit of $order->product_name because of limited stock and other reasons. Please login into your Farmsell account to search and re-order $order->name from other sellers nearest to you. Should you have any further queries or need help, please contact us.  
                   EOD;
                    $object->subject = $subject;
                    $object->name = $arr_buyer[0];
                    $object->body = $body;
                    $email_object = new OrderPlaced($object);

                    if ($buyer->email) {

                        Mail::to(strtolower($buyer->email))->send($email_object);
                    }


                    $notif = "Seller $seller->full_name has canceled the order for $order->qty $order->unit of $order->product_name";


                    $notication->NotificationHelper($buyer->id, $notif, $notif, 'OrderDetails', json_encode($notificationBody), $seller->id, $product->id);


                    $message = <<<EOD
                    Hi $arr_buyer[0], we have learnt that $arr_seller[0] is unable to supply  your order for $order->qty $order->unit of $order->product_name.  Please login into your Farmsell account https://farmsell.org/SignIn to search and re-order $order->name from other sellers nearest to you.  Regards, Farmsell 
                    EOD;

                    try {
                        if ($buyer->phone)
                            event(new SmsNotificationEvent($buyer->country_code . ltrim($buyer->phone, '0'), $message));
                    } catch (Exception $e) { {

                            Log::info($e);
                        }
                    }
                }
            }


            $order->save();

            return response()->json(['result' => true]);
        }

        return response()->json(['result' => false], 404);
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
    public function store(Save $request)
    {
        return $request->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $order = ProductOrder::find($id);

        if ($order) {
            $buyer = User::find($order->customer_id);
            $seller = User::find($order->seller_id);
            $order->seller = $seller;
            $order->buyer = $buyer;

            $prod = product::find($order->product_id);

            if ($prod) {
                $order->picture = $prod->picture;
                $order->price = $prod->price;
                $order->currency = $prod->currency;
                $order->units = $prod->units;
            }
        }

        return response()->json(['result' => true, 'order' => $order]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.get hot selling product
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getHotSellProduct()
    {
        //
        DB::statement("SET SQL_MODE=''");
        
        $order = ProductOrder::join('products', 'product_orders.product_id', '=', 'products.id')
        ->where('product_orders.order_status','6')
        ->groupBy('product_orders.product_id')
        ->orderBy(DB::raw('sum(product_orders.qty)'),'DESC')
        ->paginate(20);

        return response()->json(['result' => true, 'order' => $order]);

    }
}
