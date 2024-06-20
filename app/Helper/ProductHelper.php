<?php

namespace App\Helper;

use App\BannerGallary;
use App\category;
use App\Events\SmsNotificationEvent;
use App\Notification;
use App\product;
use App\ProductGallery;
use App\ProductViewActivities;
use App\Rating;
use App\UserHistory;
use Carbon\Carbon;
use Image;
use Exception;

class ProductHelper
{


    public function handleProductViewActivities($product, $user)
    {


        $data = [];
        //check the exististence of the logged in user in the db

        $check = ProductViewActivities::where('user_id', $user->id)->first();


        if ($check) {

            $data = json_decode($check->products);

            $status_id = in_array($product->id, $data);

            if (!$status_id) {
                array_push($data, $product->id);
                $check->products = json_encode($data);
                $check->save();
            }
        } else {

            array_push($data, $product->id);

            ProductViewActivities::create([
                'user_id' => $user->id,
                'products' => json_encode($data)

            ]);
        }
    }



    public function relatedProduct($id, $cat)
    {

        $relatedArray = [];


        $related_product = product::
            // join('regions', 'regions.id', 'products.region_id')
            // ->join('districts', 'districts.id', 'products.district_id')
            join('users', 'users.id', '=', 'products.user_id')
            ->where('products.category_id', $cat)
            ->where('products.id', '!=', $id)
            ->select(
                'products.product_title',
                'products.price',
                'products.id as product_id',
                'products.picture',
                'products.description',
                'users.id',
                'users.full_name',
                'products.longitude',
                'products.latitude',
                'products.address',
                'products.available_quantity',
                'products.units',
                'users.phone as phone',
                'users.updated_at',
                'products.view_count',
                'products.currency',
                'products.created_at as product_create_date'
            )
            ->paginate(20);


        foreach ($related_product as $product) {
            $product->product_create_date = Carbon::parse($product->product_create_date)->diffForHumans();
            array_push($relatedArray, $product);
        }

        return $relatedArray;
    }


    //product view details

    public function productDetails($id)
    {


        $details = product::join('users', 'users.id', '=', 'products.user_id')
            // ->join('districts', 'districts.id', '=', 'products.district_id')
            ->where('products.id', $id)->select(
                'products.product_title',
                'products.price',
                'products.id as product_id',
                'products.picture',
                'products.user_id',
                'products.description',
                'products.category_id',
                'products.sub_category_id',
                'products.created_at as product_create_date',
                'users.id',
                'users.full_name',
                'products.longitude',
                'products.latitude',
                'products.address',
                'products.available_quantity',
                'users.verification_status',
                'products.units',
                'users.phone as phone',
                'users.picture as photo',
                'users.avatar_google',
                'users.created_at as join_date',
                'users.country_code',
                'users.updated_at',
                'products.view_count',
                'products.currency'
            )->first();

        if ($details) {
            $category = category::find($details->category_id);
            if ($category) {

                $details->category = $category;
            }
        }


        return $details;
    }


    //product rating Helper function

    public function productRating($id)
    {
        $rating = 0;



        $rating_count = Rating::where('product_id', $id)->count();

        $ratings = Rating::where('product_id', $id)->get();

        foreach ($ratings as $rate) {
            $rating = $rating + $rate->rating;
        }

        if ($rating != 0)
            $rating = $rating / $rating_count;


        return $rating;
    }

    public function onLineStatus($data)
    {

        $string  = $data->updated_at->diffForHumans();
        $string = trim(preg_replace('!\s+!', ' ', $string));
        $array_of_words = explode(" ", $string);
        $number = (int)$array_of_words[0];
        $seconds = $array_of_words[1];


        if ($number < 59 && ($seconds == 'seconds' || $seconds == 'second')) {
            $online_status = 'online';
        } else {
            $online_status = $data->updated_at->diffForHumans();
        }

        return $online_status;
    }

    //add to product to user history




    //User Historyadd function

    public function add_to_User_History($product_id, $user_id)
    {



        $find_User_History = UserHistory::where('product_id', $product_id)->where('user_id', $user_id)->first();

        if ($find_User_History) {

            return;
        }
        $User_History = new UserHistory;

        $User_History->user_id = $user_id;
        $User_History->product_id = $product_id;
        $User_History->save();

        return;
    }




    //Get Home page banner

    public function getBanner()
    {


        $arrayId = [];
        $data = [];

        $banners = BannerGallary::all();


        foreach ($banners as $c) {

            $status_id = in_array($c->category_id, $arrayId);

            if ($status_id) {
            } else {
                array_push($arrayId, $c->category_id);
                $category = category::find($c->category_id);
                if ($category) {
                    $c->name = $category->category_name;
                }
                array_push($data, $c);
            }
        }

        return $data;
    }

    //product upload helper function

    public function add_advert_image($request, $prod)
    {

        $product = $prod;
        $path = '';

        if ($request->picture) {

            //return "Ok"

            try {

                //convert image to webp

                for ($i = 0; $i < $request->picture; $i++) {

                    $file_name = 'picture' . $i;

                    $fileName = md5(microtime()) . '_product.' . 'webp';


                    $image_path =  public_path("storage/product/" . $fileName);



                    //open image
                    $image_main = Image::make($request->$file_name);


                    //encode the image 
                    $image_main->encode('webp');
                    //$image->resize(650, null);

                    $image_main->resize(780, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                    //save the full image view data
                    $image_main->save($image_path);





                    //image path for the cover image
                    $image_cover_path =  public_path("storage/cover/" . $fileName);


                    //open image
                    $image_cover = Image::make($request->$file_name);

                    //encode the image 
                    $image_cover->encode('webp');
                    //$image->resize(650, null);

                    $image_cover->resize(300, 300, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                    //save the full image view data
                    $image_cover->save($image_cover_path);

                    //Image::make($request->$file_name)->resize(320, 240)->save($image_cover_path);




                    if ($i == 0) {
                        $path = $fileName;
                    } else {

                        $gallery = new ProductGallery();
                        $gallery->product_id = $product->id;
                        $gallery->image_path = $fileName;
                        $gallery->save();
                    }
                }


                return $path;
            } catch (Exception $ex) {


                //send message to ivan
                event(new SmsNotificationEvent('+256775496240', 'Hello Ivan, Webp conversion error was encountered?'));
                return $this->saveImageOriginalFormatWeb($request, $prod);
            }
        }
    }



    public function saveImageOriginalFormatWeb($request, $prod)
    {


        $product = $prod;

        if ($request->picture) {

            //return "Ok";



            try {

                //convert image to webp

                for ($i = 0; $i < $request->picture; $i++) {

                    $file_name = 'picture' . $i;

                    $fileName = md5(microtime()) . '_product.' . $request->$file_name->getClientOriginalExtension();


                    $image_path =  public_path("storage/product/" . $fileName);



                    //open image
                    $image_main = Image::make($request->$file_name);


                    //encode the image 
                    $image_main->encode('webp');
                    //$image->resize(650, null);

                    $image_main->resize(780, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                    //save the full image view data
                    $image_main->save($image_path);





                    //image path for the cover image
                    $image_cover_path =  public_path("storage/cover/" . $fileName);


                    //open image
                    $image_cover = Image::make($request->$file_name);

                    //encode the image 
                    $image_cover->encode('webp');
                    //$image->resize(650, null);

                    $image_cover->resize(300, 300, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                    //save the full image view data
                    $image_cover->save($image_cover_path);


                    if ($i == 0) {
                        $path = $fileName;
                    } else {

                        $gallery = new ProductGallery();
                        $gallery->product_id = $product->id;
                        $gallery->image_path = $fileName;
                        $gallery->save();
                    }

                    return $path;
                }
            } catch (Exception $ex) {

                event(new SmsNotificationEvent('+256775496240', 'Hello Ivan, Product upload by a client  failed?'));
            }
        }
    }


    //notification helper function 
    public function NotificationHelper($user_id, $body, $mobile, $screen, $param, $userId, $productId)
    {
        //add in app notification 

        try {
            Notification::create([
                'body' => $body,
                'mobile_data' => $mobile,
                'screen' => $screen,
                'screen_object' => $param,
                'user_id' => $user_id,
                'profile_id' => $userId ? $userId : null,
                'product_id' => $productId ? $productId : null
            ]);
        } catch (Exception $e) {
        }
    }
}
