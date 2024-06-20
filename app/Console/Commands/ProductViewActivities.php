<?php

namespace App\Console\Commands;

use App\Helper\ProductHelper;
use App\Mail\EmailNotification;
use App\product;
use App\ProductViewActivities as AppProductViewActivities;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;


class ProductViewActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product-view:view';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Product view activity console';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {



        //get all the view records 

        $views = AppProductViewActivities::all();

        $notication = new ProductHelper();


        //send an email for users who view a product with a delay of 8 hours 

        foreach ($views as $c) {

            $product_title = '';
            $title_mobile = '';
            $product_id = '';

            $time = Carbon::createFromFormat('Y-m-d H:i:s', $c->created_at)->diffInMinutes(\Carbon\Carbon::now());

            //Log::info($time);

            if ($time > 480 && $time < 495) {
                //Log::info("First");

                //get user user who viewed the product
                $viewer = User::find($c->user_id);

                $products = json_decode($c->products);

                $array_length = count($products);


                foreach ($products as $key => $c_2) {

                    $product_get = product::find($c_2);

                    $link = 'https://farmsell.org/ViewProduct/' . $c_2;

                    $space = ($array_length - 1) == $key && $array_length != 1 ? ' and' : ($array_length == 1 ? '' : ' ,  ');

                    $title = $product_get ? $product_get->product_title : '';

                    $product_title .=  " <a href=\"$link\"> $title </a> $space";

                    if ($key == 0) {
                        $title_mobile = $product_get->title;
                        $product_id = $c_2;
                    }
                }




                if ($viewer && $c->status == null) {

                    $message = 'Farmsell is checking if you are interested in purchasing ' . $product_title . ' you viewed';

                    $email_object = new EmailNotification($viewer->full_name, $message);
                    try {

                        //send notification

                        Mail::to($viewer->email)->send($email_object);
                        $c->status = 1;
                        $c->save();


                        //Add the bell notification
                        $message = 'Farmsell is checking if you are interested in purchasing ' . $title_mobile . ' you viewed';
                        //Add the bell notification

                        $object = (object) array(
                            'product_id' => $product_id,

                        );

                        $notication->NotificationHelper($viewer->id, $message, $message, 'Product', json_encode($object), null,$product_id );

                        //Add the bell notification

                        // Notification::create(['body' => $message, 'user_id' => $viewer->id, 'count_status' => 0]);
                    } catch (Exception $e) {
                        Log::info($e);
                    }
                }
            }
        }


        //send an email for users who view a product with a delay of 24 hours 




        foreach ($views as $key => $c) {

            $product_title = '';
            $title_mobile = '';
            $product_id = '';



            $time = Carbon::createFromFormat('Y-m-d H:i:s', $c->created_at)->diffInMinutes(\Carbon\Carbon::now());

            if ($time >= 1440 && $time <= 1455) {

                Log::info("second");

                //get user user who viewed the product
                $viewer = User::find($c->user_id);
                $products = json_decode($c->products);

                $array_length = count($products);


                foreach ($products as $key => $c_2) {

                    $product_get = product::find($c_2);
                    $link = 'https://farmsell.org/ViewProduct/' . $c_2;
                    $space = ($array_length - 1) == $key && $array_length != 1 ? ' and' : ($array_length == 1 ? '' : ' ,  ');

                    $title = $product_get ? $product_get->product_title : '';

                    $product_title .=  " <a href=\"$link\"> $title </a> $space";

                    if ($key == 0) {
                        $title_mobile = $product_get->title;
                        $product_id = $c_2;
                    }
                }



                if ($viewer && ($c->status == 1 || $c->status == null)) {

                    $message = 'Farmsell is checking if you are interested in purchasing ' . $product_title . ' you viewed';

                    $email_object = new EmailNotification($viewer->full_name, $message);

                    try {



                        //send notification

                        Mail::to($viewer->email)->send($email_object);

                        //Add the bell notification

                        //Add the bell notification
                        $message = 'Farmsell is checking if you are interested in purchasing ' . $title_mobile . ' you viewed';
                        //Add the bell notification

                        $object = (object) array(
                            'product_id' => $product_id,

                        );

                        $notication->NotificationHelper($viewer->id, $message, $message, 'Product', json_encode($object), null, $product_id);



                        // Notification::create(['body' => $message, 'user_id' => $viewer->id, 'count_status' => 0]);
                        $c->status = 2;
                        $c->save();
                    } catch (Exception $e) {
                        Log::info($e);
                    }
                }
            }
        }



        //send an email for users who view a product with a delay of 48 hours 



        foreach ($views as $key => $c) {

            $product_title = '';

            $title_mobile = '';

            $product_id = '';

            $time = Carbon::createFromFormat('Y-m-d H:i:s', $c->created_at)->diffInMinutes(\Carbon\Carbon::now());

            if ($time >= 2880 && $time <= 2895) {
                Log::info("Third");

                //get user user who viewed the product
                $viewer = User::find($c->user_id);

                $products = json_decode($c->products);
                $array_length = count($products);

                foreach ($products as $key => $c_2) {

                    $product_get = product::find($c_2);
                    $link = 'https://farmsell.org/ViewProduct/' . $c_2;
                    $space = ($array_length - 1) == $key && $array_length != 1 ? ' and' : ($array_length == 1 ? '' : ' ,  ');

                    $title = $product_get ? $product_get->product_title : '';


                    $product_title .=  " <a href=\"$link\"> $title </a> $space";

                    if ($key == 0) {
                        $title_mobile = $product_get->title;
                        $product_id = $c_2;
                    }
                }



                if ($viewer && ($c->status == 2 || $c->status == 1 || $c->status == null)) {

                    $message = 'Farmsell is checking if you are interested in purchasing ' . $product_title . ' you viewed';

                    $email_object = new EmailNotification($viewer->full_name, $message);

                    try {

                        //send notification

                        Mail::to($viewer->email)->send($email_object);

                        //Add the bell notification
                        $message = 'Farmsell is checking if you are interested in purchasing ' . $title_mobile . ' you viewed';
                        //Add the bell notification

                        $object = (object) array(
                            'product_id' => $product_id,

                        );

                        $notication->NotificationHelper($viewer->id, $message, $message, 'Product', json_encode($object) , null, $product_id);


                        //::create(['body' => $message, 'user_id' => $viewer->id, 'count_status' => 0]);

                        $c->status = 3;
                        $c->save();
                    } catch (Exception $e) {
                        Log::info($e);
                    }
                }
            }
        }




        //send an email for users who view a product with a delay of 120 hours 




        foreach ($views as $key => $c) {

            $product_title = '';
            $title_mobile = '';
            $product_id = '';

            $time = Carbon::createFromFormat('Y-m-d H:i:s', $c->created_at)->diffInMinutes(\Carbon\Carbon::now());

            if ($time >= 7200) {

                Log::info("Forth");

                //get user user who viewed the product
                $viewer = User::find($c->user_id);

                $products = json_decode($c->products);

                $array_length = count($products);


                foreach ($products as $key => $c_2) {

                    $product_get = product::find($c_2);
                    $link = 'https://farmsell.org/ViewProduct/' . $c_2;

                    $space = ($array_length - 1) == $key && $array_length != 1 ? ' and' : ($array_length == 1 ? '' : ' ,  ');

                    $title = $product_get ? $product_get->product_title : '';


                    if ($key == 0) {
                        $title_mobile = $product_get->title;
                        $product_id = $c_2;
                    }


                    $product_title .=  " <a href=\"$link\"> $title </a> $space";
                }



                if ($viewer) {

                    $message = 'Farmsell is checking if you are interested in purchasing ' . $product_title . ' you viewed';

                    $email_object = new EmailNotification($viewer->full_name, $message);
                    try {

                        //send notification

                        Mail::to($viewer->email)->send($email_object);


                        $message = 'Farmsell is checking if you are interested in purchasing ' . $title_mobile . ' you viewed';
                        //Add the bell notification

                        $object = (object) array(
                            'product_id' => $product_id,

                        );

                        $notication->NotificationHelper($viewer->id, $message, $message, 'Product', json_encode($object),null, $product_id);

                        //  Notification::create(['body' => $message, 'user_id' => $viewer->id, 'count_status' => 0]);
                    } catch (Exception $e) {
                        Log::info($e);
                    }
                }

                //Delete the view record after 120 hours notification

                $c->delete();
            }
        }
    }
}
