<?php

namespace App\Http\Requests\Orders;

use App\Events\SmsNotificationEvent;
use App\Helper\ProductHelper;
use App\Mail\OrderPlaced;
use App\Notification;
use App\product;
use App\ProductOrder;
use App\User;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Save extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'product_id' => 'required',
            'qty' => 'required',
            'unit' => 'required',

            'phone' => 'required',
            'amount' => 'required',
            'address' => 'required',
            'name' => 'required',


        ];
    }


    public function save()
    {


        $notication = new ProductHelper();

        if ($this->id) {
        } else {


            //find the product
            $prod = product::find($this->product_id);


            $order = ProductOrder::create([
                'product_id' => $this->product_id,
                'qty' => $this->qty,
                'unit' => $this->unit,
                'customer_id' => auth()->user()->id,
                'instruction' => $this->instruction,
                'phone' => $this->phone,
                //'product_name' => $this->product_name,
                'seller_id' => $prod->user_id,
                'amount' => $this->amount,
                'address' => $this->address,
                'name' => $this->name,
            ]);


            $order_num = '#' . $this->generateRandomString(3) . '-' . $this->generateRandomString(3) . '-' . $this->generateRandomString(2) . $order->id;

            //Save order number 
            $order->order_number = $order_num;





            $order->product_name = $prod->product_title;

            $order->save();

            //use the product user_id to find user 

            $prod_owner = User::find($prod->user_id);


            $body = <<<EOD
            Thanks for buying at Farmsell. We confirm the receipt of your order $order_num for $order->qty $order->unit of $order->product_name with $prod_owner->full_name from the Farmsell Marketplace.  $prod_owner->full_name will contact you shortly to finalize the arrangements to deliver your order soonest. Please make sure that your phone and email address are valid and accessible. Should you have any further queries or need help, please contact us. 
            EOD;

            $order->body = $body;
            $order->subject = "Your Farmsell Order $order_num for $order->qty $order->unit of $order->product_name";

            $email_object = new OrderPlaced($order);

            //send an email to the person placing an order 
            if (auth()->user()->email) {
                Mail::to(strtolower(auth()->user()->email))->send($email_object);
            }


            //add in app notification  to the buyer
            $order->picture = $prod->picture;

            $order->price = $prod->price;
            $order->currency = $prod->currency;
            $order->product_id = $prod->id;
            $order->units = $prod->units;
            $order->buyer = auth()->user();
            $order->seller = $prod_owner;

            $notication->NotificationHelper(auth()->user()->id, $body, $body, 'purchaseDetails', json_encode($order), false, $prod->id);


            //send an email to the product owner regarding order placing 

            $link = 'https://farmsell.org/SignIn';

            $linkText = "<a href=\"$link\">  Please login into your Farmsell account </a>";

            $body = <<<EOD
         
            Here is great news for you. $prod_owner->full_name has placed an order $order_num for  $order->qty $order->unit of $order->product_name from you on Farmsell marketplace.  $linkText to engage $prod_owner->full_name immediately to finalize the arrangements for this purchase. Should you have any further queries or need help, please contact us.  
            EOD;

            $order->body = $body;
            $order->name  = $prod_owner->full_name;
            //$order->subject=""

            $email_object = new OrderPlaced($order);

            //send an email to the person placing an order 
            if ($prod_owner->email) {
                Mail::to(strtolower($prod_owner->email))->send($email_object);
            }

            //add in app notification  for the buyer
            

            $notication->NotificationHelper($prod_owner->id, $body, $body, 'OrderDetails', json_encode($order), auth()->user()->id, $prod->id);

            $buyer = auth()->user();

            $arr_buyer = explode(' ', trim($buyer->full_name));
            $arr_seller = explode(' ', trim($prod_owner->full_name));


            $message = <<<EOD
            Hi $arr_seller[0], You have received an order for $order->qty $order->unit of $order->product_name from $arr_buyer[0].  Please login into your Farmsell account https://farmsell.org/SignIn to engage $arr_buyer[0]  immediately to finalize the arrangements for this purchase.  Regards, Farmsell  
            EOD;


            //phone notification to the seller 
            try {
                if ($prod_owner->phone)
                    event(new SmsNotificationEvent($prod_owner->country_code . ltrim($prod_owner->phone, '0'), $message));
            } catch (Exception $e) {
                Log::info($e);
            }

            return response()->json(['result' => true]);
        }
    }


    function generateRandomString($length = 10)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
