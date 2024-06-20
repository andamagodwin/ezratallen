<?php

namespace App\Http\Controllers;

use App\BannerGallary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Events\MessageEvent;
use App\category;
use App\CategoryBanner;
use App\subCategory;
use App\region;
use App\district;
use App\product;
use App\Chat;
use App\ChatHistory;
use App\WishList;
use App\UserHistory;
use App\Reports;
use App\NewsLetterEmail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Image;
use Carbon\Carbon;
use App\Comment;
use App\Events\NewsLetterEvent;
use App\Events\ProductAddEvent;
use App\Events\SmsNotificationEvent;
use App\Follow;
use App\Helper\FcmNotification;
use App\Helper\ProductHelper;
use App\Jobs\compressImages;
use App\Listeners\SendSmsNotification;
use App\Mail\EmailNotification;
use App\Mail\MessageNotification;
use App\Mail\ProductAddedEvent;
use Illuminate\Support\Facades\Hash;
use App\Reply;
use App\Rating;
use App\Notification;
use App\OauthAccessToken;
use App\Page;
use App\PageContent;
use App\ProductGallery;
use App\ProductViewLog;
use App\ProductLikeLog;
use App\ProductOrder;
use App\ReviewReply;
use App\Sellers;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use stdClass;

class UserController extends Controller
{
    //profile update function 


    public  function profile_update(Request $request, $id)
    {

        // find the user 
        $user = User::find($id);

        if ($request->has('full_name'))
            $user->full_name = $request->full_name;

        if ($request->has('phone'))
            $user->phone = $request->phone;

        if ($request->has('email'))
            $user->email = $request->email;
        $user->save();



        return response(['user' => $user]);
    }

    public function picture_update(Request $request, $id)
    {

        // find the user 
        $user = User::find($id);


        if ($request->hasFile('image')) {

            //get existing image path
            $image_path = public_path("storage/{$user->picture}");


            //Delete existing image in the file system
            if ($user->picture && File::exists($image_path)) {
                unlink($image_path);
            }
        }
        $path = $request->file('image')->store('avatars', ['disk' => 'public']);


        if ($path) {

            //store the image url in database 

            $user->picture = $path;
            $user->save();


            //Return success response 
            return response(['url' => $path, 'user' => $user]);
        } else {
            return response(['error' => "Failed to updated profile"]);
        }
    }


    //getting adverts/products

    public function get_products_by_category(Request $request, $id)
    {

        if (!region::find($id))
            return "No adverts available";

        $product = category::find($id)->products;

        echo $product;
    }


    public function get_products_by_sub_category(Request $request, $id)
    {

        if (!region::find($id))
            return "No adverts available";

        $product = subCategory::find($id)->products;

        echo $product;
    }

    public function get_products_by_region(Request $request, $id)
    {

        if (!region::find($id))
            return "No adverts available";


        $product = region::find($id)->products;

        echo $product;
    }



    public function get_products_by_district(Request $request, $id)
    {


        $product = district::find($id)->products;

        echo $product;
    }

    public function add_news_letter_email(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }
        $check_email = NewsLetterEmail::where('email', $request->email)->first();

        if ($check_email) {
            return response()->json(['error' => true]);
        }

        $NewsLetterEmail = NewsLetterEmail::create($request->all());
        event(new NewsLetterEvent($request->email, $request->name));
        return response()->json([
            'NewsLetterEmail' => $NewsLetterEmail,
            'success' => "NewsLetterEmail saved succesfully", 'message' => true
        ]);
    }

    public function add_report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required'
        ]);

        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $newReport = $request->all();

        $fileName = null;

        if ($request->hasFile('picture')) {


            $fileName = md5(microtime()) . '_report.' . $request->picture->getClientOriginalExtension();
            $image_path =  public_path("storage/reports/" . $fileName);
            Image::make($request->picture)->save($image_path);
        }

        $report = Reports::create($newReport);

        $report = Reports::where('id', $report->id)->first();
        $report->picture = $fileName;
        $report->save();


        //$user = User::find($request->user_id);

        return response()->json(['Reports' => $report]);
    }


    //Add advert function

    public function add_advert(Request $request)
    {


        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'sub_category_id' => 'required',
            // 'region_id' => 'required',
            'user_id' => 'required',
            // 'district_id'=>'required',
            'picture' => 'required',
            'product_title' => 'required',
            'price' => 'required',
            'description' => 'required',
            //'negotiation_status' => 'required',
            // 'phone_number'=>'required',
            'units' => 'required',
            'available_quantity' => 'required',
            'currency' => 'required',
            // 'Unit_cost'=>'required',
        ]);


        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }




        /**Store all values of the fields
         */
        if (!$request->negotiation_status) {
            $request->negotiation_status = 0;
        }

        $newAdvert = $request->all();

        $newAdvert['is_approved'] = 0;


        $path = null;




        /*
        if ($request->hasFile('picture')) {


            $fileName = md5(microtime()) . '_product.' . $request->picture->getClientOriginalExtension();

            $image_path =  public_path("storage/product/" . $fileName);
            Image::make($request->picture)->save($image_path);

            $image_cover_path =  public_path("storage/cover/" . $fileName);
            Image::make($request->file('picture'))->resize(320, 240)->save($image_cover_path);

            $path = $fileName;
        }
        */



        /**Insert a new user in the table
         */
        $product = product::create($newAdvert);

        //save the product picture url in the database 


        $helper = new ProductHelper();


        //upload advert image helper function that returns product

        $path = $helper->add_advert_image($request, $product);


        /*

        if ($request->picture) {
            //return "Ok";




            for ($i = 0; $i < $request->picture; $i++) {

                $file_name = 'picture' . $i;

                $fileName = md5(microtime()) . '_product.' . $request->$file_name->getClientOriginalExtension();
                $image_path =  public_path("storage/product/" . $fileName);

                Image::make($request->$file_name)->save($image_path);

                $image_cover_path =  public_path("storage/cover/" . $fileName);

                Image::make($request->$file_name)->resize(320, 240)->save($image_cover_path);


                if ($i == 0) {
                    $path = $fileName;
                } else {

                    $gallery = new ProductGallery();
                    $gallery->product_id = $product->id;
                    $gallery->image_path = $fileName;
                    $gallery->save();
                }
            }
        }
        */


        $product = product::where('id', $product->id)->first();
        $product->picture = $path;
        $product->save();
        $user = User::find($request->user_id);


        if ($request->phone && $request->country_code) {

            $user->phone = $request->phone;
            $user->country_code = $request->country_code;
            $user->save();
        }



        if ($user) {

            $notication = new ProductHelper();


            $objectNotification = (object) array(
                'product_id' => $product->id
            );


            $body = "Hi " . $user->full_name . " Your product has being succesfully added, Once it's reviewed and approved, your product will be ready for public view ";



            $notication->NotificationHelper($user->id, $body, $body, 'Product', json_encode($objectNotification), false, $product->id);



            $body =  "Your product has being succesfully added, Once it's reviewed and approved, your product will be ready for public view ";


            //send to the user 
            event(new ProductAddEvent($user->email, $user->full_name, $body, $product->product_title));

            //send an email alert to the farmsell  admin

            //  $body =  "A new farmsell product(" . $product->product_title . ") has been uploaded by " . $user->full_name . " and it now awaits your approval";

            // event(new ProductAddEvent('ivanmundruku@gmail.com', 'Farmsell Admin', $body, $product->product_title));

            //$admins = User::where('user_type', 'admin')->get();

            /*
            foreach ($admins as $admin) {

                $body =  "
                 A new farmsell product(" . $product->product->title . ") has been uploaded by
                  " . $user->full_name . "and it now awaits your approval";

                if ($admin->email) {
                    event(new ProductAddEvent($admin->email, $admin->full_name, $body));
                }
            }
            */
        }

        return response()->json(['product' => $product]);
    }



    //Temporarily adding adding product for mobile app

    public function add_advert_mobile(Request $request)
    {
        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'sub_category_id' => 'required',
            // 'region_id' => 'required',
            // 'user_id' => 'required',
            // 'district_id'=>'required',
            'picture' => 'required',
            'product_title' => 'required',
            //'price' => 'required',
            'description' => 'required',
            //'negotiation_status' => 'required',
            // 'phone_number'=>'required',

            'units' => 'required',
            'available_quantity' => 'required',
            'currency' => 'required',
            'address' => 'required',
            // 'Unit_cost'=>'required',
        ]);


        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }




        /**Store all values of the fields
         */
        $newAdvert = $request->all();
        $newAdvert['is_approved'] = $request->is_approved ? $request->is_approved : 0;
        $newAdvert['price'] = $request->price ? $request->price : 0;
        $newAdvert['user_id'] = auth()->user()->id;


        $path = null;

        /**Insert a new user in the table
         */
        $product = product::create($newAdvert);




        $helper = new ProductHelper();


        //upload advert image helper function that returns product

        $path = $helper->add_advert_image($request, $product);




        $product = product::where('id', $product->id)->first();

        $product->picture = $path;

        $product->save();
        $user = User::find($request->user_id);


        if ($request->phone && $request->country_code) {

            $user->phone = $request->phone;
            $user->country_code = $request->country_code;
            $user->save();
        }


        if ($user && $user->email) {

            $notication = new ProductHelper();


            $objectNotification = (object) array(
                'product_id' => $product->id
            );


            $body = "Hi " . $user->full_name . " Your product has being succesfully added, Once it's reviewed and approved, your product will be ready for public view ";

            $notication->NotificationHelper($user->id, $body, $body, 'Product', json_encode($objectNotification), false, $product->id);

            $body =  "Your product has being succesfully added, Once it's reviewed and approved, your product will be ready for public view ";

            //send to the user 
            event(new ProductAddEvent($user->email, $user->full_name, $body, $product->product_title));

                //send an email alert to the farmsell  admin

                // $body =  "A new farmsell product(" . $product->product_title . ") has been uploaded by " . $user->full_name . " and it now awaits your approval";

                // event(new ProductAddEvent('ivanmundruku@gmail.com', 'Farmsell Admin', $body, $product->product_title));

                //$admins = User::where('user_type', 'admin')->get();

                /*
            foreach ($admins as $admin) {


                $body =  "
                 A new farmsell product(" . $product->product->title . ") has been uploaded by
                  " . $user->full_name . "and it now awaits your approval";

                if ($admin->email) {
                    event(new ProductAddEvent($admin->email, $admin->full_name, $body));
                }
            }
            */;
        }
        return response()->json(['product' => $product, 'user' => $user]);
    }








    //get product for edit 

    public function get_product_for_edit($id)
    {

        $product = product::join('categories', 'products.category_id', '=', 'categories.id')
            ->join('sub_categories', 'products.sub_category_id', '=', 'sub_categories.id')
            ->select(
                'products.id as id',
                'products.category_id',
                'products.sub_category_id',
                'products.product_title',
                'products.negotiation_status',
                'products.description',
                'products.picture',
                'products.address',
                'products.price',
                'products.available_quantity',
                'products.units',
                'products.currency',
                'categories.category_name',
                'sub_categories.subcat_name'
            )
            ->where('products.id', $id)
            ->first();
        $gallery = ProductGallery::where('product_id', $id)->get();

        if ($product) {
            return response()->json([
                'product' => $product,
                'gallery' => $gallery

            ]);
        }

        return response()->json(['error' => 'No product found for edit']);
    }

    //editing an advert function

    public function edit_advert(Request $request)
    {


        /**Store all values of the fields
         */
        $product = product::find($request->product_id);


        $path = null;



        if ($request->picture) {
            //return "Ok";

            for ($i = 0; $i < $request->picture; $i++) {
                $file_name = 'picture' . $i;

                $fileName = md5(microtime()) . '_product.' . $request->$file_name->getClientOriginalExtension();
                $image_path =  public_path("storage/product/" . $fileName);
                Image::make($request->$file_name)->save($image_path);

                $image_cover_path =  public_path("storage/cover/" . $fileName);

                Image::make($request->$file_name)->resize(320, 240)->save($image_cover_path);


                if (!$request->picture_url) {
                    $path = $fileName;

                    $product->picture = $path;
                }

                if ($request->picture_url) {

                    $gallery = new ProductGallery();
                    $gallery->product_id = $product->id;
                    $gallery->image_path = $fileName;
                    $gallery->save();
                }
            }
        }


        if ($request->product_title != null)
            $product->product_title = $request->product_title;

        if ($request->category_id != null)
            $product->category_id = $request->category_id;

        if ($request->sub_category_id != null)
            $product->sub_category_id = $request->sub_category_id;

        //  if($request->region_id!=null)
        //  $product->region_id_id=$request->region_id;

        //  if($request->district_id!=null)
        //  $product->district_id=$request->district_id;

        if ($request->price != null)
            $product->price = $request->price;

        if ($request->description != null)
            $product->description = $request->description;

        //  if($request->phone_number!=null)
        //  $product->phone_number=$request->phone_number;

        if ($request->longitude != null)
            $product->longitude = $request->Longitude;

        if ($request->latitude != null)
            $product->latitude = $request->Latitude;

        if ($request->quantity_available != null)
            $product->quantity_available = $request->quantity_available;

        if ($request->units != null)
            $product->units = $request->units;

        if ($request->currency != null)
            $product->currency = $request->currency;

        if ($request->address != null)
            $product->address = $request->address;

        if ($request->negotiation_status != null)
            $product->negotiation_status = $request->negotiation_status;


        $product->is_approved = 0;


        $product->save();

        return response()->json(['edit_product' => $product]);
    }


    public function deleteProductImageFirst($id)
    {

        $product = product::find($id);
        if ($product) {

            //$path = $request->file('picture')->store('product', ['disk' => 'public']);
            //get existing image path
            $image_path = public_path("storage/{$product->picture}");

            //$product->picture = $path;
            //Delete existing image in the file system
            if ($product->picture && File::exists($image_path)) {
                unlink($image_path);
            }
            $banner = ProductGallery::where('product_id', $id)->first();
            if ($banner) {
                $product->picture = $banner->image_path;
                $product->save();
                return response()->json(['result', true]);
            }

            $product->picture = 'false';

            $product->save();

            return response()->json(['result', true]);
        }
    }


    public function deleteProductImage($id)
    {

        $product = ProductGallery::find($id);
        if ($product) {

            //$path = $request->file('picture')->store('product', ['disk' => 'public']);
            //get existing image path
            $image_path = public_path("storage/{$product->image_path}");

            //$product->picture = $path;
            //Delete existing image in the file system
            if ($product->image_path && File::exists($image_path)) {
                unlink($image_path);
            }

            $product->delete();


            return response()->json(['result', true]);
        }

        return response()->json(['result', false]);
    }




    //New search implementation for the header 

    public function search_header(Request $request)
    {

        $product_name = $request->key;

        $products = product::where('product_title', 'LIKE', '%' . $product_name . '%')
            ->orWhere('address',  'LIKE', '%' . $product_name . '%')
            ->where('is_approved', 1)
            ->limit(10)
            ->select('product_title', 'address', 'id')
            ->get();

        $users = User::where('users.full_name',  'LIKE', '%' . $product_name . '%')
            ->limit(4)
            ->get();

        return response()->json([
            'product' => $products,

            'user' => $users
        ]);
    }


    //Search by price


    public function advanced_searching(Request $request)
    {

        $product_name = $request->key;

        $products = product::where('is_approved', 1)
            ->where('product_title', 'LIKE', '%' . $product_name . '%')
            ->orWhere('address',  'LIKE', '%' . $product_name . '%')

            ->paginate(15);

        $users = User::join('products', 'users.id', '=', 'products.user_id')
            ->where('full_name',  'LIKE', '%' . $product_name . '%')
            ->where('products.is_approved', 1)
            ->select(
                'products.id',
                'products.product_title',
                'products.price',
                'products.address',
                'products.view_count',
                'products.units',
                'products.price',
                'products.currency',
                'products.picture'
            )
            ->paginate(15);

        // Search 

        return response()->json(['product' => $products, 'user' => $users]);
    }




    //advert search function 


    public function advert_search(Request $request)
    {


        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'key' => 'required',

        ]);


        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $product_name = $request->key;

        if ($request->category_id) {
            $list = product::where('product_title', 'LIKE', '%' . $product_name . '%')
                ->where('is_approved', 1)
                ->where('category_id', $request->category_id)->get();

            return response()->json(['advert_list' => $list]);
        }

        $list = product::where('product_title', 'LIKE', '%' . $product_name . '%')
            ->where('is_approved', 1)
            ->get();

        return response()->json(['advert_list' => $list]);
    }


    public function shceduleJob()
    {
    }


    //single product details function


    // public  function single_product(Request $request, $id){




    //     $product=product::find($id);

    //     if($product){
    //         $related_product=product::
    //         // join('regions', 'regions.id', 'products.region_id')
    //         // ->join('districts', 'districts.id', 'products.district_id') 
    //         // 
    //         join('users', 'users.id', '=', 'products.user_id')
    //         ->where('products.sub_category_id', $product->sub_category_id)
    //         ->where('products.id', '!=', $id)
    //         ->select('products.product_title', 'products.price', 'products.id as product_id','products.Longitude',
    //      'products.picture', 'products.description','users.id', 'users.full_name', 
    //      'products.phone_number as phone', 'users.updated_at', 'products.view_count', 'products.Longitude', 'products.Latitude')
    //         ->get();
    //     }


    //     $single_product=product::join('users', 'users.id', '=', 'products.user_id')
    //     ->join('districts', 'districts.id', '=', 'products.district_id') 
    //     ->where('products.id', $id)->select('products.product_title', 'products.price', 'products.id as product_id',
    //      'products.picture', 'products.description','users.id', 'users.full_name',  
    //      'products.phone_number as phone', 'users.updated_at', 'products.view_count','products.Longitude','products.Latitude')->get();

    //      $product_details=[];
    //      $rating=0;

    //      foreach($single_product as $product){


    //        if($request->user_id){
    //            $user_rate_status=Rating::where('user_id', $request->user_id)->where('product_id', $id)->first();
    //            if($user_rate_status)
    //            {
    //                $product->user_rate_status=true;
    //            }
    //            else{
    //                $product->user_rate_status=false;
    //            }
    //        }
    //        $rating_count=Rating::where('product_id', $id)->count();
    //        $ratings=Rating::where('product_id', $id)->get();

    //        foreach($ratings as $rate){
    //         $rating=$rating+$rate->rating;
    //        }
    //       if($rating!=0)
    //      $rating=$rating/$rating_count;


    //       $product->rating=$rating;
    //         if($product->id!=$id)
    //         {
    //             $pro=product::find($id);
    //             $pro->view_count+=1;
    //             $pro->save();

    //         }

    //         $string  = $single_product[0]->updated_at->diffForHumans();
    //         $string = trim(preg_replace('!\s+!', ' ', $string));
    //         $array_of_words = explode(" ", $string);
    //         $number=(int)$array_of_words[0];
    //         $seconds=$array_of_words[1];


    //         if($number<59 && $seconds=='seconds' ){
    //             $product->online_status='online';
    //         }
    //         else{
    //             $product->online_status=$single_product[0]->updated_at->diffForHumans();
    //         }

    //          array_push($product_details, $product);


    //      }

    //      $comment_count=Comment::where('product_id', $id)->count();

    //     if($single_product){
    //         return response()->json([ 'details'=> $product_details, 'comment_count'=>$comment_count, 'related_product'=>$related_product]);
    //     }

    // }



    //related product pagination


    public function relatedProductPagination($id)
    {

        $product = product::find($id);

        $helper = new ProductHelper();

        $relatedArray = [];


        //get related products from the related product helper function
        if ($product) {

            $relatedArray =  $helper->relatedProduct($id, $product->category_id);
        }


        return response()->json(['related_product' => $relatedArray]);
    }





    //product view page 
    public  function single_product(Request $request, $id)
    {




        /*
        $message = 'Your product ' . 'goat' . ' has  been viewed by ' . 'mundruku'. '.';

        $email_object = new EmailNotification('Mundruku', $message);

        Mail::to('ivanmundruku@gmail.com')->send($email_object);

        $var='mmdmdmdmd';
        */





        $product = product::find($id);


        //return when the product is not found 

        if (!$product) {

            return response()->json(['error' => true], 400);
        }

        //initialize the product helper object to access reusable functions 

        $helper = new ProductHelper();


        //product count 
        $product_count = product::where('user_id', $product->user_id)->count();
        //Followers count 
        $followers_count = Follow::where('follower_id', $product->user_id)->count();


        //check  wishlist

        $find_wishlist = WishList::where('product_id', $id)->where('user_id', $request->viewer_id)->first();

        $favorite_status = false;
        if ($find_wishlist) {
            $favorite_status = true;
        }


        $relatedArray = [];


        //get related products from the related product helper function
        if ($product) {

            $relatedArray =  $helper->relatedProduct($id, $product->category_id);
        }

        //get product details

        $product_details = $helper->productDetails($id);

        //add product browsing history for the user 


        //return response()->json(['product'=>$product_details]);


        //Check user rate status so that he/she can not rate it again

        if ($request->user_id) {

            $user_rate_status = Rating::where('user_id', $request->viewer_id ? $request->viewer_id : $request->user_id)->where('product_id', $id)->first();
            if ($user_rate_status) {
                $product_details->user_rate_status = true;
            } else {
                $product_details->user_rate_status = false;
            }
        }

        //product rating value acquired from the product Rating method

        $product_details->rating = $helper->productRating($id);

        //Online status

        $product_details->online_status = $helper->onLineStatus($product_details);
        //End

        $product_details->product_create_date = Carbon::parse($product_details->product_create_date)->diffForHumans();



        $category = category::find($product_details->category_id);

        $sub_cat = subCategory::find($product_details->sub_category_id);
        $product_details->favorite_status = $favorite_status;
        $product_details->category = $category ? $category->category_name : 'N/F';
        $product_details->sub_category = $sub_cat ? $sub_cat->subcat_name : 'N/F';

        //$pro = product::find($id);

        $rating_details = Rating::join('users', 'users.id', '=', 'ratings.user_id')
            ->where('ratings.product_id', $product_details->product_id)
            ->select(
                'ratings.review',
                'users.full_name',
                'ratings.product_id',
                'ratings.user_id',
                'ratings.rating',
                'ratings.id',
                'ratings.created_at'

            )

            ->get();



        //get the logged in user and check with ProductViewLog
        $viewer = 'a new vistor';





        $allow_view_count = false;

        //$delay_hours = 0;

        $logged_user = User::find($request->viewer_id ? $request->viewer_id : $request->user_id);

        $ip_address = $request->ip();






        //Check view recormds when the user is logged in

        if ($logged_user) {



            //check if the request is comming from the web application

            if ($request->user_id) {

                $viewLog = ProductViewLog::where('product_id', $id)
                    ->where('ip_address', $ip_address)
                    ->where('user_id', $request->user_id)
                    //->orWhere('user_id', $request->viewer_id)
                    //->latest('created_at')
                    ->first();
            } else {

                $viewLog = ProductViewLog::where('product_id', $id)
                    ->where('ip_address', $ip_address)
                    ->where('user_id', $request->viewer_id)
                    //->orWhere('user_id', $request->viewer_id)
                    //->latest('created_at')
                    ->first();
            }


            $logged_user->updated_at = Carbon::now();
            $logged_user->save();
            $helper->add_to_User_History($id, $logged_user->id);
        } else {

            $viewLog = ProductViewLog::where('product_id', $id)
                ->where('ip_address', $ip_address)
                // ->latest('created_at')
                ->first();
        }


        if ($viewLog) {


            $time = Carbon::createFromFormat('Y-m-d H:i:s', $viewLog->created_at)->diffInMinutes(\Carbon\Carbon::now());

            if ($time > 360) {

                $allow_view_count = true;

                $viewLog->created_at = Carbon::now();

                $viewLog->save();
            } else {
                $allow_view_count = false;
            }

            /*
            $lastView = Carbon::createFromFormat('Y-m-d H:s:i', $viewLog->created_at);

            $timeNow = Carbon::now();

            $diff = $lastView->diffInHours($timeNow);

            $allow_view_count = $diff >= 1; //Notify when a customer views the product after 1hr

            $delay_hours = $diff;
            */
        } else {
            $allow_view_count = true;
        }


        //handle the product viewer 
        if ($logged_user && $logged_user->id != $product->user_id) { //viewer is logged in
            //The logged in user is not the product owner

            $helper->handleProductViewActivities($product, $logged_user);


            $allow_view = $product->user_id != $logged_user->id;

            if ($allow_view) {

                $viewer = $logged_user->full_name;
            }
        }


        if ($allow_view_count) {

            //  return;
            $product->view_count += 1;

            $product->save();

            if (!$viewLog) {

                ProductViewLog::create([
                    'product_id' => $product->id,
                    'user_id' => empty($logged_user) ? null : $logged_user->id,
                    'ip_address' => $ip_address,
                ]);
            }

            $user = User::find($product->user_id);



            //$message = 'Your product ' . $product->product_title . ' has  been viewed by ' . $viewer . '.';

            $message = ' Your product has been viewed by '  . $viewer . '. Reach out to them by starting a chat in messages to find out if they are interested in buying your product.';


            /*
            Notification::create([
                'body' => $message,
                'user_id' => $user->id,
                'count_status' => 0
            ]);
            */

            $notication = new ProductHelper();


            $objectNotification = (object) array(
                'product_id' => $id
            );


            $email_object = new EmailNotification($user->full_name, $message);






            try {


                $notication->NotificationHelper($user->id, $message, $message, 'Product', json_encode($objectNotification), $viewer ? $viewer->id : '', $product->id);


                ///Notify after 5hrs
                if ($user->email)
                    Mail::to($user->email)->later(now()->addMinutes(3000), $email_object); //5hrs

                /*
                if (!empty($logged_user) && $user->phone)
                    event(new SmsNotificationEvent($user->country_code . ltrim($user->phone, '0'), $message));
                    */
            } catch (Exception $e) {

                Log::info($e);
            }

            //$when = now()->addMinutes(3000);//5hrs
            //$user->notify((new QueueMessage($user->email, $email_object))->delay($when));



        }

        $seller = Sellers::where('seller_id', $product->user_id)->first();

        if ($seller) {
            $product_details->full_name = $seller->b_name;
        }


        $comment_count = Comment::where('product_id', $id)->count();

        $rating_data = $this->product_rating_records($id, $request->user_id);

        $comment_data = Comment::join('users', 'users.id', '=', 'comments.user_id')
            ->where('comments.product_id', $id)
            ->select('comments.id', 'comments.user_id', 'comments.product_id', 'comments.comment', 'comments.created_at', 'comments.updated_at', 'users.full_name', 'users.avatar_google', 'users.picture')
            ->take(3)
            ->get();

        $gallery = ProductGallery::where('product_id', $id)->get();


        //get the users favorites count

        $wish_list_count = 0;

        if ($request->user_id || $request->viewer_id) {
            $wish_list_count = WishList::where('user_id',  $request->viewer_id ? $request->viewer_id : $request->user_id)->count();
        }
        $followed = false;
        if ($request->user_id) {
            $followed = Follow::where('user_id', $product->user_id)
                ->where('follower_id', $request->user_id)
                ->first();
        }


        return response()->json([
            'details' => $product_details,
            'followers' => $followers_count,
            'comment_count' => $comment_count,
            'product_count' => $product_count,

            'related_product' => $relatedArray,
            'rating_data' => $rating_data,
            'comment' => $comment_data,
            'gallery' => $gallery,
            'rating' => $rating_details,
            'favorites_count' => $wish_list_count,
            'followed' => $followed ? true : false

        ]);
    }



    public function getNotificationAndMessageCount()
    {

        $user = auth()->user();

        $notification_count = Notification::where('user_id', $user->id)
            ->where('status', '0')->count();

        $notification_order = Notification::where('user_id', $user->id)
            ->where('notification_type', '1')
            ->where('status', '0')->count();



        return response()->json([
            'notification_count' => $notification_count,
            'order_status' => $notification_order

        ]);
    }




    public function getHomePageData(Request $request)
    {
        $helper = new ProductHelper();

        $banner = $helper->getBanner();

        if ($request->mobile == 1) {

            $recent = product::where('is_approved', 1)
                ->orderBy('created_at', 'DESC')

                ->paginate(20);
            $popular = product::where('is_approved', 1)

                ->orderBy('popular_result', 'DESC')

                ->paginate(20);

            // return response()->json(['product' => $products]);
        } else {

            $recent = product::where('is_approved', 1)
                ->orderBy('created_at', 'DESC')->paginate(30);


            $popular = product::where('is_approved', 1)
                ->orderBy('popular_result', 'DESC')->paginate(30);
        }


        $categories = CategoryBanner::join(
            'categories',
            'categories.id',
            '=',
            'category_banners.category_id'
        )
            ->select(
                'categories.id',
                'categories.description',
                'categories.category_name',
                'category_banners.picture_url'
            )
            ->orderBy('categories.id', 'Asc')
            ->get();
        $notification_count = 0;

        $subcategories = subCategory::all();


        if ($request->user_id) {
            $notification_count = Notification::where('user_id', $request->user_id)
                ->where('status', '0')->count();
        }

        $suppliers = Sellers::paginate(20);

        return response()->json([
            'banner' => $banner,
            'popular' => $popular,
            'recent' => $recent,
            'notification_count' => $notification_count,
            'category' => $categories,
            'sub' => $subcategories,
            'suppliers' => $suppliers
        ]);
    }


    public function get_product_gallery($id)
    {

        $gallery = ProductGallery::where('product_id', $id)->get();

        return response()->json(['items' => $gallery]);
    }


    public function get_Recent_adverts(Request $request)
    {


        if ($request->mobile == 1) {
            $products = product::where('is_approved', 1)
                ->orderBy('created_at', 'DESC')

                ->paginate(20);
            return response()->json(['product' => $products]);
        }

        $products = product::where('is_approved', 1)
            ->orderBy('created_at', 'DESC')->paginate(30);


        if ($products) {

            return response()->json(['product' => $products]);
        }
    }

    public function get_the_popular_adverts(Request $request)
    {



        if ($request->mobile == 1) {
            $products = product::where('is_approved', 1)

                ->orderBy('popular_result', 'DESC')

                ->paginate(20);
            return response()->json(['product' => $products]);
        }

        $products = product::where('is_approved', 1)
            ->orderBy('popular_result', 'DESC')->paginate(30);

        if ($products) {
            return response()->json(['product' => $products]);
        }
    }

    //gettting hot deals 


    public function get_hot_deals(Request $request)
    {

        if ($request->mobile == 1) {
            $products = product::where('is_approved', 1)
                ->orderBy('view_count', 'DESC')->paginate(20);
            return response()->json(['product' => $products]);
        }

        $products = product::where('is_approved', 1)
            ->orderBy('view_count', 'DESC')->paginate(30);

        if ($products) {
            return response()->json(['product' => $products]);
        }
    }


    //getting categories, subcategories, region and subcategories

    public  function get_categories(Request $request)
    {

        $categories = CategoryBanner::join(
            'categories',
            'categories.id',
            '=',
            'category_banners.category_id'
        )
            ->select(
                'categories.id',
                'categories.description',
                'categories.category_name',
                'category_banners.picture_url'
            )
            ->orderBy('categories.id', 'Asc')
            ->get();

        $sub = subCategory::all();

        return response()->json([
            'categories' => $categories,
            'sub_categories' => $sub,
            'status' => true,
        ]);
    }

    public  function get_categories_slide(Request $request)
    {


        $categories = CategoryBanner::join(
            'categories',
            'categories.id',
            '=',
            'category_banners.category_id'
        )
            ->select(
                'categories.id',
                'categories.description',
                'categories.category_name',
                'category_banners.picture_url'
            )
            ->orderBy('categories.id', 'Asc')
            ->get();
        if ($categories) {
            return response()->json(['categories' => $categories]);
        }
        if ($request->mobile == 1) {
            $categories = category::all();
            return response()->json(['categories' => $categories]);
        }



        $categories = category::all();

        if ($categories) {
            return response()->json(['categories' => $categories]);
        }
    }


    public function get_categories_filter(Request $request, $id)
    {

        $categories = product::where('category_id', $id)
            ->where('is_approved', 1)
            ->get();

        if ($categories) {
            return response()->json(['categories' => $categories]);
        }
    }


    public function get_subcategories(Request $request, $id)
    {

        $subcategories = product::where('sub_category_id', $id)
            ->where('is_approved', 1)
            ->get();

        if ($subcategories) {
            return response()->json(['subcategories' => $subcategories]);
        }
    }

    public function get_subcategories_upload(Request $request, $id)
    {

        $subcategories = subCategory::where('category_id', $id)->get();

        if ($subcategories) {
            return response()->json(['subcategories' => $subcategories]);
        }
    }


    public function get_regions(Request $request)
    {

        $regions = region::all();

        if ($regions) {
            return response()->json(['region' => $regions]);
        }
    }



    public function get_districts(Request $request, $id)
    {

        $districts = district::where('region_id', $id)->get();

        if ($districts) {
            return response()->json(['districts' => $districts]);
        }
    }

    public function get_districts_all(Request $request)
    {

        $districts = district::all();
        if ($districts) {
            return response()->json(['districts' => $districts]);
        }
    }

    public function get_subcategories_all(Request $request)
    {

        $subcat_all = subCategory::all();

        if ($subcat_all) {
            return response()->json(['sub_cat' => $subcat_all]);
        }
    }


    public function renew_product_date(Request $request)
    {

        $product = product::find($request->id);

        if ($product) {
            $product->created_at = Carbon::now();
            $product->save();

            return response()->json(['result' => true]);
        }

        return response()->json(['result' => false]);
    }


    public function close_open_product(Request $request)
    {

        $product = product::find($request->id);

        if ($product) {
            $product->is_approved = $request->status;
            $product->save();

            return response()->json(['result' => true]);
        }

        return response()->json(['result' => false]);
    }




    public function update_available_quanty(Request $request)
    {
        $product = product::find($request->id);

        if (!$request->status) {
            $product->available_quantity = 0;
            $product->is_approved = 3;
            $product->save();

            return response()->json(['result' => true]);
        }

        if ($product) {


            $product->available_quantity = ($request->qty +  $product->available_quantity);
            $product->is_approved = 1;
            $product->save();
            return response()->json(['result' => true]);
        }


        return response()->json(['result' => false], 404);
    }






    public function get_user_product_list($id)
    {

        $approved = product::where([
            ['user_id', '=', $id],
            ['is_approved', '=', 1],

        ])->get();

        $checking = product::where([
            ['user_id', '=', $id],
            ['is_approved', '=', 0],

        ])->get();

        $rejected = product::where([

            ['user_id', '=', $id],
            ['is_approved', '=', 2],

        ])->get();

        $closed = product::where([
            ['user_id', '=', $id],
            ['is_approved', '=', 3],

        ])->get();

        $sold = ProductOrder::where('seller_id', $id)->where('order_status', '1')->count();
        $unsold = 0;

        $product =   product::where('products.user_id', $id)
            ->where('is_approved', 1)
            ->get();

        foreach ($product as $c) {
            $order = ProductOrder::where('product_id', $c->id)

                ->where('order_status', 1)->first();
            if (!$order) {

                $unsold = $unsold + 1;
            }
        }

        $out_of_stock = product::where('available_quantity', 0)
            ->where('products.user_id', $id)
            ->count();


        $object = (object) array(
            'approved' => $approved,
            'checking' => $checking,
            'rejected' => $rejected,
            'closed' => $closed,
            'sold' => $sold,
            'unsold' => $unsold,
            'outstock' => $out_of_stock
        );



        return response()->json(['list' => $object]);
    }


    //get user uploaded adverts


    public function get_user_product(Request $request, $id)
    {

        if ($request->status == 1) {

            $list = product::where('user_id', $id)->orderBy('id', 'Desc')->paginate(6);

            return response()->json(['list' => $list]);
        }
        //status 2 is for New product
        if ($request->status == 2) {


            $list = product::where('user_id', $id)->orderBy('id', 'Desc')->paginate(6);

            return response()->json(['list' => $list]);
        }


        //status 3 is for sorting price in descending
        if ($request->status == 3) {

            $list = product::where('user_id', $id)->orderBy('id', 'Desc')->paginate(6);
            return response()->json(['list' => $list]);
        }

        //status 4 is for sorting price in Ascending order
        if ($request->status == 4) {

            $list = product::where('user_id', $id)->orderBy('id', 'Desc')->paginate(6);

            return response()->json(['list' => $list]);
        }


        $list = product::where('user_id', $id)->orderBy('id', 'Desc')->paginate(100);
        if ($list) {
            return response()->json(['list' => $list]);
        }
    }

    //Deleting User product

    public function  delete_user_product(Request $request, $id)
    {

        $product = product::find($id);

        if ($product) {

            $product->delete();
        }
        if ($product) {
            return response()->json(['delete' => $product]);
        }
    }




    public function deleteChat(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required',
            'receiver_id' => 'required',
            'id' => 'required',

        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $sender_id = $request->sender_id;
        $receiver_id = $request->receiver_id;

        $chat = Chat::find($request->id);



        if ($chat) {

            $array = [];

            if ($chat->delete_permission) {
                $array = json_decode($chat->delete_permission);

                array_push($array, auth()->user()->id);
            } else {
                array_push($array, auth()->user()->id);
            }

            $chat->delete_permission = json_encode($array);
            $chat->save();
        }

        $chatLatest = Chat::where('receiver_id', $receiver_id)
            ->where('sender_id', $sender_id)->latest('id')->first();

        $history = ChatHistory::where('receiver_id', $receiver_id)
            ->where('sender_id', $sender_id)->first();
        if ($chatLatest && $history && $chat) {
            $history->sender_id = $sender_id;
            $history->receiver_id = $receiver_id;
            $history->message = $chatLatest->message;
            $history->delete_permission = json_encode($array);
            $history->save();
        }

        return response()->json(['result' => true]);
    }

    //Saving chat messages 


    public function chat_message_save(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required',
            'receiver_id' => 'required',
            'type' => 'required',
            'message' => 'required',
            'delivered' => 'required',
            'status' => 'required',
        ]);

        $user = auth()->user();
        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        /**Store all values of the fields
         */
        $message_body = $request->all();
        $message_body['count_status'] = 0;

        /**Insert a message body in the database 
         */


        $message_create = Chat::create($message_body);

        if ($message_create) {
            $last_history_check = null;

            $last_history_check1 = ChatHistory::where('receiver_id', $request->receiver_id)
                ->where('sender_id', $request->sender_id)

                ->first();
            if ($last_history_check1) {
                $last_history_check = $last_history_check1;
            }

            $last_history_check2 = ChatHistory::where('receiver_id', $request->sender_id)
                ->where('sender_id',  $request->receiver_id)
                ->first();

            if ($last_history_check2) {
                $last_history_check = $last_history_check2;
            }


            if ($last_history_check) {
                $last_history_check->sender_id = $request->sender_id;
                $last_history_check->receiver_id = $request->receiver_id;
                $last_history_check->message = $request->message;
                $last_history_check->chat_id = $message_create->id;
                $last_history_check->read_status = 0;
                $last_history_check->created_at = Carbon::now();
                $last_history_check->last_send_id = $request->sender_id;
                $last_history_check->save();
            } else {
                $last_history_check = new ChatHistory();
                $last_history_check->sender_id = $request->sender_id;
                $last_history_check->receiver_id = $request->receiver_id;
                $last_history_check->message = $request->message;
                $last_history_check->chat_id = $message_create->id;
                $last_history_check->read_status = 0;
                $last_history_check->last_send_id = $request->sender_id;
                $last_history_check->save();
            }



            $receiver = User::find($request->receiver_id);
            $sender = User::find($request->sender_id);
            if ($receiver && $sender) {

                $email_object = new MessageNotification($sender->full_name, $request->message);
                Mail::to($receiver->email)->later(now()->addMinutes(5), $email_object);

                //event(new MessageEvent($request->message, $sender->full_name, $receiver->email));
            }

            if ($receiver) {

                $fcm = new FcmNotification();

                $fcmObject = (object) array(
                    'token' => $receiver->device_token,
                    'body' => $request->message,
                    'title' => $user->full_name,
                    'data' => []
                );
                $fcm->MessageToDevice($fcmObject);
            }



            if ($request->make_offer && $receiver && $receiver->email) {


                $email_object = new EmailNotification($receiver->full_name, $request->message_owner);

                ///Notify after 5hrs

                Mail::to($receiver->email)->later(now()->addMinutes(3000), $email_object);
            }

            return response()->json(['success' => 'sent', 'message_body' => $message_create], 200);
        } else
            return response()->json(['error' => 'sent']);
    }

    //getting chat messages

    public function get_chat_messages(Request $request)
    {


        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required',
            'receiver_id' => 'required',
        ]);

        $online = '';

        if ($request->history_id) {

            $history = ChatHistory::find($request->history_id);

            if ($history) {
                $history->read_status = 1;
                $history->save();
            }
        }

        Chat::where('sender_id', $request->sender_id)
            ->where('receiver_id', $request->receiver_id)
            ->update(['read_status' => 1]);

        if (auth()->user()) {
            Chat::where('receiver_id', auth()->user()->id)
                ->where('status', 0)->orWhere('status', 2)
                ->update(['status' => 1]);
        }

        $receiver = User::find($request->receiver_id);

        $sender = User::find($request->sender_id);

        $checkOnline = new ProductHelper();
        $online = $checkOnline->onLineStatus($sender);


        $string  = $sender->updated_at->diffForHumans();

        $string = trim(preg_replace('!\s+!', ' ', $string));

        $array_of_words = explode(" ", $string);

        $number = (int)$array_of_words[0];

        $seconds = $array_of_words[1];


        if ($number < 59 && ($seconds == 'seconds' || $seconds == 'second')) {
            $sender->online_status = 'online';
        } else {
            $sender->online_status = null;
        }



        //get the user in each case 
        $check = "unit";
        $initial = "unit";
        $messages = [];

        $message = Chat::where('sender_id', $request->sender_id)->orWhere('sender_id', $request->receiver_id)
            ->get();

        foreach ($message as $mes) {
            if ($mes->receiver_id == $request->sender_id || $mes->receiver_id == $request->receiver_id) {
                $date_created = Carbon::parse($mes->created_at)->diffForHumans();
                $mes->date = $date_created;
                $mes->time = Carbon::createFromFormat('Y-m-d H:i:s', $mes->created_at)->setTimezone($request->zone)->isoFormat('h:mm a');

                $created = new Carbon($mes->created_at);
                $now = Carbon::now();

                $time = $created->diff($now)->days;


                if ($check === $initial) {

                    $todayCheck = Carbon::now()->isSameDay($mes->created_at);

                    if ($todayCheck) {
                        //attache todays day
                        $mes->date_group = 'Today';
                    } else {
                        $mes->date_group = Carbon::parse($mes->created_at)->format('d M Y');
                    }
                }

                if ($time === $check) {
                    //$c->date_group = false;
                }

                //check if the dates  are the same, if not then attach another date match

                if (($check !== "unit") && ($check !== $time)) {

                    $mes->date_group = Carbon::parse($mes->created_at)->format('d M Y');
                }


                $check = $time;


                //check delete status

                if ($mes->delete_permission) {
                    $array = json_decode($mes->delete_permission);
                    $check_delete = in_array(auth()->user()->id, $array);

                    if (!$check_delete) {
                        array_push($messages, $mes);
                    }
                } else {
                    array_push($messages, $mes);
                }
            }
        }



        return response()->json([
            'messages' => $messages,
            'online' => $online,
            'receiver' => $receiver, 'sender' => $sender
        ]);
    }



    public function updateNotificationCount(Request $request)
    {

        if ($request->count) {
            Notification::where('user_id', $request->user_id)
                ->where('count_status', 0)
                ->update(['count_status' => 1]);

            return response()->json(['result' => true]);
        }

        Notification::where('user_id', $request->user_id)

            ->where('count_status', 0)
            ->update(['read_status' => 1]);

        return response()->json(['result' => true]);
    }



    //User notification retrieval

    public function getUserNotifications(Request $request)
    {

        $user = auth()->user();

        $notific_data = Notification::where('user_id', $user->id)
            //->where('read_status', 0)
            ->orderBy('id', 'DESC')
            ->get();

        //format the date

        foreach ($notific_data as $c) {
            $todayCheck = Carbon::now()->isSameDay($c->created_at);
            if ($todayCheck) {
                $date =  Carbon::createFromFormat('Y-m-d H:i:s', $c->created_at)->setTimezone($request->zone)->isoFormat('h:mm a');
                $c->date =   $date;
            } else {

                $yearCheck = Carbon::now()->isSameYear($c->created_at);
                if ($yearCheck) {
                    $date = Carbon::parse($c->created_at)->format('M d');
                } else {
                    $date = Carbon::parse($c->created_at)->format('M d, Y');
                }
                $c->date =   $date;
            }
        }

        return response()->json(['result' => true, 'notification' => $notific_data]);
    }



    //  Getting chat lists for a user

    public function  get_chat_lists(Request $request)
    {


        /*
        Chat::where('receiver_id', $request->receiver_id)->where('status', 0)
            ->update(['status' => 1]);
            */


        $user = auth()->user();

        $message_count = Chat::where('receiver_id', $user->id)
            ->where('read_status', 0)
            ->count();

        /*  $notification_count = Notification::where('user_id', $request->receiver_id)
            ->where('read_status', 0)
            ->count();
            */
        /*

        $notific_data = Notification::where('user_id', $user->id)
            //->where('read_status', 0)
            ->orderBy('id', 'DESC')
            ->get();
            */


        //update the chat and message notification count

        Chat::where('receiver_id', $user->id)
            ->where('count_status', 0)
            ->update(['count_status' => 1]);

        Notification::where('user_id', $user->id)
            ->update(['status' => '1']);

        //set delivered status

        Chat::where('receiver_id', $user->id)
            ->where('status', 0)
            ->update(['status' => 2]);

        //Get users that have sent messages
        $message_list = ChatHistory::join('users', 'chat_histories.sender_id', '=', 'users.id')
            ->where('chat_histories.receiver_id', $user->id)
            ->orWhere('chat_histories.sender_id', $user->id)
            ->select('users.id', 'users.updated_at as online', 'chat_histories.sender_id', 'chat_histories.created_at', 'chat_histories.receiver_id', 'users.full_name', 'chat_histories.message', 'users.picture', 'users.avatar_google', 'chat_histories.read_status')
            ->orderBy('chat_histories.updated_at', 'desc')
            ->get();

        $message_array = [];

        foreach ($message_list as $message) {


            $todayCheck = Carbon::now()->isSameDay($message->created_at);

            if ($todayCheck) {
                $date = Carbon::createFromFormat('Y-m-d H:i:s', $message->created_at)->setTimezone($request->zone)->isoFormat('h:mm a');
                $message->date =   $date;
            } else {

                $yearCheck = Carbon::now()->isSameYear($message->created_at);
                if ($yearCheck) {
                    $date = Carbon::parse($message->created_at)->format('M d');
                } else {
                    $date = Carbon::parse($message->created_at)->format('M d, Y');
                }
                $message->date =   $date;
            }

            // $message->date = $dated_created;

            if ($message->sender_id == $user->id) {



                $find_other_user = User::find($message->receiver_id);


                if ($find_other_user) {

                    $string  = Carbon::parse($find_other_user->updated_at)->diffForHumans();

                    $string = trim(preg_replace('!\s+!', ' ', $string));

                    $array_of_words = explode(" ", $string);

                    $number = (int)$array_of_words[0];

                    $seconds = $array_of_words[1];

                    if ($number < 59 && ($seconds == 'seconds' || $seconds == 'second')) {
                        $message->online_status = 'online';
                        $message->mobile_online = 'online';
                    } else {
                        $message->online_status = null;
                        $message->mobile_online = $string;
                    }

                    $message->history_id = $message->id;

                    $message->full_name = $find_other_user->full_name;
                    $message->avatar_google = $find_other_user->avatar_google;
                    $message->picture = $find_other_user->picture;
                    $message->id = $find_other_user->id;

                    $message->count = null;
                }
            } else {

                $count = Chat::where('sender_id', $message->sender_id)
                    ->where('receiver_id',  $message->receiver_id)
                    ->where('read_status', 0)
                    ->count();
                $string  = Carbon::parse($message->online)->diffForHumans();
                $string = trim(preg_replace('!\s+!', ' ', $string));
                $array_of_words = explode(" ", $string);
                $number = (int)$array_of_words[0];
                $seconds = $array_of_words[1];

                if ($number < 59 && ($seconds == 'seconds' || $seconds == 'second')) {
                    $message->online_status = 'online';
                    $message->mobile_online = 'online';
                } else {
                    $message->online_status = null;
                    $message->mobile_online = $string;
                }

                $message->count = $count;
            }
            array_push($message_array, $message);
        }

        return response()->json([
            'message_list' => $message_array,
            'count' => $message_count
        ]);
    }

    // All Users
    public function get_all_users(Request $request)
    {

        $users = User::where('id', '!=', $request->receiver_id)
            ->where('user_type', '!=', "admin")
            ->get();
        $result = [];

        foreach ($users as $user) {


            $string  = Carbon::parse($user->updated_at)->diffForHumans();
            $string = trim(preg_replace('!\s+!', ' ', $string));
            $array_of_words = explode(" ", $string);
            $number = (int)$array_of_words[0];
            $seconds = $array_of_words[1];

            if ($number < 59 && ($seconds == 'seconds' || $seconds == 'second')) {
                $user->online_status = 'online';
            } else {
                $user->online_status = null;
            }

            array_push($result, $user);
        }

        return response()->json(['users' => $result]);
    }

    //User search

    public function user_search(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'key' => 'required',

        ]);


        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $user_name = $request->key;


        $users = User::where('full_name', 'LIKE', '%' . $user_name . '%')
            ->where('id', '!=', $request->user_id)
            ->get();
        $result = [];

        foreach ($users as $user) {


            $string  = Carbon::parse($user->updated_at)->diffForHumans();
            $string = trim(preg_replace('!\s+!', ' ', $string));
            $array_of_words = explode(" ", $string);
            $number = (int)$array_of_words[0];
            $seconds = $array_of_words[1];

            if ($number < 59 && ($seconds == 'seconds' || $seconds == 'second')) {
                $user->online_status = 'online';
            } else {
                $user->online_status = null;
            }

            array_push($result, $user);
        }

        return response()->json(['users' => $result]);
    }

    public function unread_message(Request $request)
    {

        $unread_messages = Chat::join('users', 'chats.sender_id', '=', 'users.id')

            ->orderBy('chats.id', 'desc')
            ->where('receiver_id', $request->receiver_id)
            ->select('users.id as id', 'users.full_name', 'users.picture', 'users.avatar_google', 'chats.message')
            ->limit(5)
            ->get();
        return response()->json(['messages' => $unread_messages]);
    }



    public function markNotification($id)
    {
        $notification = Notification::find($id);

        if ($notification) {
            $notification->read_status = 1;
            $notification->save();
        }
    }

    //Delete notification

    public function delete_notification($id)
    {

        $notification = Notification::find($id);



        if ($notification) {
            $notification->delete();
            return response()->json(['result' => true]);
        }
    }

    //Get notifications 

    public function get_notification_data(Request $request)
    {




        $notifications = Notification::join('users', 'users.id', 'notifications.user_id')
            ->where('notifications.user_id', $request->user_id)
            ->select(
                'notifications.body',
                'notifications.id',
                'notifications.read_status',
                'notifications.created_at',
                'users.picture',
                'users.avatar_google'
            )
            ->get();

        Notification::where('user_id', $request->user_id)->update(['read_status' => 1]);


        return response()->json(['notification' => $notifications]);
    }

    //Set unread status for a notification

    public function update_notification_status(Request $request)
    {
        $notification = Notification::find($request->notification_id);

        if ($notification) {

            $notification->read_status = 1;
            $notification->save();

            return response()->json(['success' => true, 'data' => $notification]);
        }
    }

    public function unread_notification_count(Request $request)
    {

        $count = Chat::where('receiver_id', $request->user_id)->where('count_status', 0)->count();

        $notification_count = Notification::where('user_id', $request->user_id)
            ->where('count_status', 0)
            ->count();

        return response()->json(['count' => $count, 'notification_count' => $notification_count]);
    }

    // unread mmessage count

    public function unread_messages_count(Request $request)
    {

        $count = Chat::where('receiver_id', $request->user_id)->where('read_status', 0)->count();

        $product_count = Chat::where('receiver_id', $request->user_id)
            ->where('read_status', 0)
            ->count();

        $notification_count = Notification::where('user_id', $request->user_id)
            ->where('read_status', 0)
            ->count();

        return response()->json(['count' => $count + $product_count, 'notification_count' => $notification_count]);
    }

    //unread messaga status update

    public function unread_messages_status_update(Request $request)
    {
        $updata_status = Chat::where('receiver_id', $request->receiver_id)->update(array('status' => 1));

        return response()->json(['success' => 'update succesfull']);
    }

    //updation read status

    public function read_messages_status_update(Request $request)
    {
        $updata_status = Chat::where('receiver_id', $request->receiver_id)->where('sender_id', $request->sender_id)->update(array('read_status' => 1));
        return response()->json(['success' => 'update succesfull']);
    }

    //product chat user
    public function product_chat_user(Request $request)
    {


        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required',
            'receiver_id' => 'required',
        ]);
        $receiver = User::find($request->receiver_id);

        $sender = User::find($request->sender_id);
        $messages = [];

        $message = Chat::where('sender_id', $request->sender_id)->orWhere('sender_id', $request->receiver_id)
            ->get();

        foreach ($message as $mes) {
            if ($mes->receiver_id == $request->sender_id || $mes->receiver_id == $request->receiver_id)
                array_push($messages, $mes);
        }



        return response()->json(['messages' => $messages, 'receiver' => $receiver, 'sender' => $sender]);
    }

    //Online/ last seen status function

    public function online_last_seen(Request $request)
    {

        $user = User::find($request->user_id);
        $user->touch();
    }

    //Adding product comment

    public function product_comment(Request $request)
    {


        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'product_id' => 'required',
            'comment' => 'required',
        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        /**Store all values of the fields
         */
        $comment_body = $request->all();

        /**Insert a message body in the database 
         */
        $comment_create = Comment::create($comment_body);

        if ($comment_create)
            return response()->json(['message_body' => $comment_create], 200);
        else
            return response()->json(['error' => 'sent']);
    }

    //get product comment 

    public function get_product_comment(Request $request)
    {
        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);
        /**Check the validation fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $comment = Comment::join('users', 'users.id', '=', 'comments.user_id')
            ->where('comments.product_id', $request->product_id)
            ->select('comments.id', 'comments.user_id', 'comments.product_id', 'comments.comment', 'comments.created_at', 'comments.updated_at', 'users.full_name', 'users.avatar_google', 'users.picture')
            ->get();
        $comments = [];
        foreach ($comment as $comment) {
            $count = Reply::where('comment_id', $comment->id)->count();
            $comment->count = $count;

            array_push($comments, $comment);
        }

        return response()->json(['comment' => $comments]);
    }





    //Adding product comment reply

    public function add_product_comment_reply(Request $request)
    {


        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'comment_id' => 'required',
            'product_id' => 'required',
            'reply_body' => 'required',
            'repliers_id' => 'required'
        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        /**Store all values of the fields
         */
        $reply_body = $request->all();

        /**Insert a message body in the database 
         */
        $reply_create = Reply::create($reply_body);

        if ($reply_create)
            return response()->json(['reply_body' => $reply_create], 200);
        else
            return response()->json(['error' => 'reply not sent']);
    }


    //get product comment replies 

    public function get_product_comment_reply(Request $request)
    {
        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'comment_id' => 'required',
        ]);
        /**Check the validation fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $replies = Reply::join('users', 'users.id', '=', 'replies.repliers_id')
            ->where('replies.product_id', $request->product_id)
            ->where('comment_id', $request->comment_id)
            ->get();


        return response()->json(['replies' => $replies]);
    }

    //wish list add function

    public function add_to_wishlist(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'user_id' => 'required'
        ]);
        /**Check the validation fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $find_wishlist = WishList::where('product_id', $request->product_id)->where('user_id', $request->user_id)->first();

        if ($find_wishlist) {
            $find_wishlist->delete();

            return null;
        }
        $wish_list = new WishList;

        $wish_list->user_id = $request->user_id;
        $wish_list->product_id = $request->product_id;

        if ($wish_list->save()) {
            return response()->json(['success' => true]);
        }

        return response()->json(['message' => false]);
    }


    //wish list get function

    function get_wishlist(Request $request)
    {

        /**Validate the data using validation rules
         */
        // $validator = Validator::make($request->all(), [

        //     'user_id' => 'required'
        // ]);
        $user_id = auth()->user()->id;
        /**Check the validation fails or not*/
        // if ($validator->fails()) {
        //     /**Return error message
        //      */
        //     return response()->json(['error' => $validator->errors()]);
        // }

        $wish_list = WishList::join('products', 'products.id', '=', 'wish_lists.product_id')
            ->where('wish_lists.user_id', $user_id)->get();


        return response()->json(['wish_list' => $wish_list, 'status' => true]);
    }

    //Deleting User product

    public function  delete_wishList_product(Request $request)
    {

        $user_id = auth()->user()->id;
        $wish_list = WishList::where(
            'product_id',

            $request->product_id
        )
            ->where(
                'user_id',

                $user_id
            )


            ->first();

        if ($wish_list) {

            $wish_list->delete();
        }
        if ($wish_list) {
            return response()->json(['delete' => $wish_list]);
        }
    }



    //User Historyadd function

    public function add_to_User_History(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'user_id' => 'required'
        ]);
        /**Check the validation fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $find_User_History = UserHistory::where('product_id', $request->product_id)->where('user_id', $request->user_id)->first();

        if ($find_User_History) {

            return null;
        }
        $User_History = new UserHistory;

        $User_History->user_id = $request->user_id;
        $User_History->product_id = $request->product_id;

        if ($User_History->save()) {
            return response()->json(['success' => 'product added succesfully']);
        }

        return response()->json(['error' => 'Failed']);
    }


    //wish list get function

    function get_User_History(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [

            'user_id' => 'required'
        ]);
        /**Check the validation fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $historyArray = [];

        $User_History = UserHistory::join('products', 'products.id', '=', 'user_histories.product_id')
            ->where('user_histories.user_id', $request->user_id)

            ->select(
                'products.product_title',
                'products.price',
                'products.id',
                'products.picture',
                'products.view_count',
                'user_histories.id as history_id',
                'products.created_at as date_created',

                'products.address',


                'products.units',
                'products.currency'
            )->get();


        foreach ($User_History as $history) {

            $history->date_created = Carbon::parse($history->date_created)->diffForHumans();


            array_push($historyArray, $history);
        }



        return response()->json(['user_history' => $historyArray]);
    }

    //Deleting one record from history
    public function  delete_user_single_history(Request $request)
    {

        //UserHistory::where('product_id', $request->product_id)->delete();

        $history = UserHistory::find($request->id);

        if ($history) {
            $history->delete();
            return response()->json(['delete' => true]);
        }
    }

    //Deleting User History

    public function  delete_user_history(Request $request)
    {


        UserHistory::where('user_id', $request->user_id)->delete();

        return response()->json(['message' => true]);
    }



    //adding reply to a review

    public function addReviewReply(Request $request)
    {

        $reply = new ReviewReply();
        $reply->rating_id = $request->rating_id;
        $reply->reply = $request->reply;
        $reply->save();

        return response()->json(['result' => true]);
    }




    //get product rating status

    public function productRatingStatus($id)
    {
        $user = auth()->user();

        $order = ProductOrder::where('product_id', $id)->where('customer_id', $user->id)->first();


        if ($order) {
            return response()->json(['result' => true]);
        }

        return response()->json(['result' => false]);

        //
    }



    //get product rating details


    public function product_rating_details(Request $request, $id)
    {

        $helper = new ProductHelper();



        $avg_rating = $helper->productRating($id);
        $five_star = Rating::join('users', 'users.id', '=', 'ratings.user_id')
            ->where('ratings.rating', '>=', 5)
            ->where('ratings.product_id', $id)
            ->select(
                'ratings.review',
                'users.full_name',
                'ratings.product_id',
                'ratings.user_id',
                'ratings.rating',
                'ratings.id',
                'ratings.created_at'
            )

            ->get();
        $four_star = Rating::join('users', 'users.id', '=', 'ratings.user_id')
            ->where('ratings.rating', '>=', 4)
            ->where('ratings.rating', '<', 5)
            ->where('ratings.product_id', $id)
            ->select(
                'ratings.review',
                'users.full_name',
                'ratings.product_id',
                'ratings.user_id',
                'ratings.rating',
                'ratings.id',
                'ratings.created_at'
            )
            ->get();
        $three_star = Rating::join('users', 'users.id', '=', 'ratings.user_id')
            ->where('ratings.rating', '>=', 3)
            ->where('ratings.rating', '<', 4)
            ->where('ratings.product_id', $id)
            ->select(
                'ratings.review',
                'users.full_name',
                'ratings.product_id',
                'ratings.user_id',
                'ratings.rating',
                'ratings.id',
                'ratings.created_at'
            )
            ->get();
        $two_star = Rating::join('users', 'users.id', '=', 'ratings.user_id')
            ->where('ratings.rating', '>=', 2)

            ->where('ratings.rating', '<', 3)
            ->where('ratings.product_id', $id)
            ->select(
                'ratings.review',
                'users.full_name',
                'ratings.product_id',
                'ratings.user_id',
                'ratings.rating',
                'ratings.id',
                'ratings.created_at'
            )
            ->get();
        $one_star = Rating::join('users', 'users.id', '=', 'ratings.user_id')
            ->where('ratings.rating', '<=', 1)
            ->where('ratings.product_id', $id)
            ->select(
                'ratings.review',
                'users.full_name',
                'ratings.product_id',
                'ratings.user_id',
                'ratings.rating',
                'ratings.id',
                'ratings.created_at'
            )
            ->get();

        $all = Rating::join('users', 'users.id', '=', 'ratings.user_id')
            ->where('ratings.product_id', $id)
            ->select(
                'ratings.review',
                'users.full_name',
                'ratings.product_id',
                'ratings.user_id',
                'ratings.rating',
                'ratings.id',
                'ratings.created_at'

            )

            ->get();

        $edit = false;
        $user = false;


        $user = Rating::where('user_id', $request->user_id)
            ->where('product_id', $id)
            ->first();

        if ($user) {
            $edit = $user;
        }




        $order = ProductOrder::where('product_id', $id)->where('customer_id', $request->user_id)->first();


        $object = (object) array(
            'avg_rating' => $avg_rating,
            'five_star' => $five_star,
            'four_star' => $four_star,
            'three_star' => $three_star,
            'two_star' => $two_star,
            'one_star' => $one_star,
            'all' => $all,
            'edit' => $edit,
            'rating_status' => $order ? true : false
        );

        return response()->json(['details' => $object]);
    }


    //Product rating api for the web


    public function product_rating_mobile(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [

            'user_id' => 'required',
            'product_id' => 'required',
            'rating' => 'required'
        ]);
        /**Check the validation fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }


        $rating_get = Rating::where('user_id', $request->user_id)
            ->where('product_id', $request->product_id)->first();




        if ($rating_get) {
            $rating_get->rating = $request->rating;
            $rating_get->review = $request->review;
            $rating_get->save();

            return response()->json(['success' => 'Rating succesfull']);
        }


        $find_product_owner_status = product::find($request->product_id);

        if ($find_product_owner_status && $find_product_owner_status->user_id == $request->user_id) {
            return response()->json(['error' => 'You can not rate your own product']);
        }


        $rating = new Rating;

        $rating->user_id = $request->user_id;
        $rating->product_id = $request->product_id;
        $rating->rating = $request->rating;
        $rating->review = $request->review;

        if ($rating->save()) {
            return response()->json(['success' => 'Rating succesfull']);
        }

        return response()->json(['error' => 'Rating failed']);
    }


    //Product rating api for the web


    public function product_rating(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [

            'user_id' => 'required',
            'product_id' => 'required',
            'rating' => 'required'
        ]);
        /**Check the validation fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        if ($request->rated_count) {
            $rating_get = Rating::where('user_id', $request->user_id)
                ->where('product_id', $request->product_id)->first();

            if ($rating_get) {
                $rating_get->rating = $request->rating;
                $rating_get->save();

                return response()->json(['success' => 'Rating succesfull']);
            }
        }

        $find_product_owner_status = product::find($request->product_id);

        if ($find_product_owner_status && $find_product_owner_status->user_id == $request->user_id) {
            return response()->json(['error' => 'You can not rate your own product']);
        }

        $find_user_rating_status = Rating::where('user_id', $request->user_id)->where('product_id', $request->product_id)->first();

        if ($find_user_rating_status) {
            return response()->json(['error' => 'You have already rated the product']);
        }

        $rating = new Rating;

        $rating->user_id = $request->user_id;
        $rating->product_id = $request->product_id;
        $rating->rating = $request->rating;

        if ($rating->save()) {
            return response()->json(['success' => 'Rating succesfull']);
        }

        return response()->json(['error' => 'Rating failed']);
    }

    //features to track product viewers
    function log_product_views(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'product_id' => 'required'
        ]);

        /**Check the validation fails or not*/
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $logs = ProductViewLog::where(['user_id' => $request['user_id'], 'product_id' => $request['product_id']])->first();
        if (empty($logs)) $logs->save();
    }

    function log_product_likes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'product_id' => 'required'
        ]);

        /**Check the validation fails or not*/
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $logs = ProductLikeLog::where(['user_id' => $request['user_id'], 'product_id' => $request['product_id']])->first();
        if (empty($logs)) $logs->save();
    }

    function get_product_views(Request $request)
    {
        $list = ProductViewLog::where(['product_id' => $request['product_id']])->get();
        return response()->json($list);
    }

    function get_product_likes(Request $request)
    {
        $list = ProductLikeLog::where(['product_id' => $request['product_id']])->get();
        return response()->json($list);
    }


    //deleting user account

    function delete_user_account(Request $request)
    {


        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [

            'user_id' => 'required'
        ]);
        /**Check the validation fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }
    }

    //Get product rating data for all the rating ranges

    public function product_rating_records($product_id, $user_id)
    {



        $total_rating = Rating::where('product_id', $product_id)->count();
        $sum_of_rating = Rating::where('product_id', $product_id)->sum('rating');

        $five_star = Rating::where('product_id', $product_id)
            ->where('rating', 5)
            ->count();
        $four_star = Rating::where('product_id', $product_id)
            ->where('rating', 4)
            ->count();
        $three_star = Rating::where('product_id', $product_id)
            ->where('rating', 3)
            ->count();
        $two_star = Rating::where('product_id', $product_id)
            ->where('rating', 2)
            ->count();
        $one_star = Rating::where('product_id', $product_id)
            ->where('rating', 1)
            ->count();
        //intializing the rating ranges
        $five_avg_percentage = 0;
        $four_avg_percentage = 0;
        $three_avg_percentage = 0;
        $two_avg_percentage = 0;
        $one_avg_percentage = 0;
        $avg_rating = 0;


        //Five star average in percentage
        if ($five_star != 0 && $total_rating != 0)
            $five_avg_percentage = ($five_star / $total_rating) * 100;

        if ($four_star != 0 && $total_rating != 0)
            $four_avg_percentage = ($four_star / $total_rating) * 100;

        if ($three_star != 0 && $total_rating != 0)
            $three_avg_percentage = ($three_star / $total_rating) * 100;

        if ($two_star != 0 && $total_rating != 0)
            $two_avg_percentage = ($two_star / $total_rating) * 100;

        if ($one_star != 0 && $total_rating != 0)
            $one_avg_percentage = ($one_star / $total_rating) * 100;
        //Average rating 

        if ($sum_of_rating != 0 && $total_rating != 0)
            $avg_rating = $sum_of_rating / $total_rating;


        $rating_data = new stdClass();

        $rating_data->average_rating = $avg_rating;
        $rating_data->five_star = $five_avg_percentage;
        $rating_data->four_star = $four_avg_percentage;
        $rating_data->three_star = $three_avg_percentage;
        $rating_data->two_star = $two_avg_percentage;
        $rating_data->one_star = $one_avg_percentage;
        $rating_data->rated_star_number = 0;

        $rated_data = Rating::where('product_id', $product_id)
            ->where('user_id', $user_id)->first();

        if ($rated_data) {
            $rating_data->rated_star_number = $rated_data->rating;
        }



        return $rating_data;
    }


    //logout user when the token is invalid

    public function invalid_token(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [

            'user_id' => 'required',
            'token' => 'required',
        ]);
        /**Check the validation fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $token_response = OauthAccessToken::where('user_id', $request->user_id)->first();

        if ($token_response) {
            return response()->json(['status' => true]);
        } else {
            return response()->json(['status' => false]);
        }
    }


    public function update_notification_status_mass(Request $request)
    {


        Notification::where('user_id', $request->receiver_id)
            ->update(['read_status' => 1]);


        Notification::where('user_id', $request->receiver_id)
            ->update(['status' => '1']);

        return response()->json(['result' => true]);
    }


    public function get_page_data($id)
    {
        $page = PageContent::where('page_id', $id)->get();
        return  response()->json(['page' => $page]);
    }


    public function get_pages()
    {
        $data = [];
        $page = Page::all();


        foreach ($page as $c) {
            $page_content = PageContent::where('page_id', $c->id)->get();

            $c->page_content = $page_content;
            array_push($data, $c);
        }

        return  response()->json(['page' => $data]);
    }



    public  function api_testing(Request $request)

    {



        /*
        if ($request->receiver_id) {
            return "Ok";
        } else {
            return "false";
        }
        */


        /*

    
       return  compressImages::dispatchNow();



        $product = product::all();


        foreach ($product as $c) {

            try {

                $path = public_path("storage/product/" . $c->picture);



                if (File::exists($path)) {

                    $fileName = md5(microtime()) . '_product.' . 'webp';

                    $image_path =  public_path("storage/cover/" . $fileName);

                    $image =  Image::make($path);
                    //resize the image 


                    //encode the image 
                    $image->encode('webp');
                    //$image->resize(650, null);

                    $image->resize(300, 300, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $image->save($image_path);



                    $image_path =  public_path("storage/prod/" . $fileName);

                    $image_main =  Image::make($path);
                    //resize the image 


                    //encode the image 
                    $image_main->encode('webp');
                    //$image->resize(650, null);

                    $image_main->resize(780, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $image_main->save($image_path);


                    $c->picture = $fileName;
                    $c->save();
                }
            } catch (Exception $e) {





                return response()->json(['error'=>$e]);

                $path = public_path("storage/product/" . $c->picture);

                if (File::exists($path)) {

                    $fileName = md5(microtime()) . '_product.' . $path->getClientOriginalExtension();

                    $image_path =  public_path("storage/cover/" . $fileName);

                    $image =  Image::make($path);
                    //resize the image 


                    //encode the image 
                    $image->encode('webp');
                    //$image->resize(650, null);

                    $image->resize(300, 300, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $image->save($image_path);



                    $image_path =  public_path("storage/prod/" . $fileName);

                    $image_main =  Image::make($path);
                    //resize the image 


                    //encode the image 
                    $image_main->encode('webp');
                    //$image->resize(650, null);

                    $image_main->resize(780, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $image_main->save($image_path);


                    $c->picture = $fileName;
                    $c->save();
                }
            }
        }



        return "Images uploaded";
        */



        if ($request->picture) {
            //return "Ok";

            // for ($i = 0; $i < $request->picture; $i++) {

            $file_name = 'picture' . '.webp';

            $fileName = md5(microtime()) . '_product.' . 'webp';
            $image_path =  public_path("storage/test/" . $fileName);


            //open  the image

            //return $image_path;


            $image =  Image::make($request->picture);
            //resize the image 


            //encode the image 
            $image->encode('webp');
            //$image->resize(650, null);

            $image->resize(750, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $image->save($image_path);

            return "Ok";

            $image_cover_path =  public_path("storage/test2/" . $fileName);

            Image::make($request->$file_name)->resize(320, 240)->save($image_cover_path);



            /*
                if ($i == 0) {
                    $path = $fileName;
                } else {

                    $gallery = new ProductGallery();
                    $gallery->product_id = $product->id;
                    $gallery->image_path = $fileName;
                    $gallery->save();
                }
                */
            //  }
        }

        /*
        $single_product = product::join('users', 'users.id', '=', 'products.user_id')
            // ->join('districts', 'districts.id', '=', 'products.district_id')
            ->where('products.id', $request->id)->select(
                'products.product_title',
                'products.price',
                'products.id as product_id',
                'products.picture',
                'products.user_id',
                'products.description',
                'products.created_at as product_create_date',
                'users.id',
                'users.full_name',
                'products.longitude',
                'products.latitude',
                'products.address',
                'products.available_quantity',
                'products.units',
                'users.phone as phone',
                'users.picture as photo',
                'users.avatar_google',
                'users.updated_at',
                'products.view_count',
                'products.currency'
            )->get();

        return response()->json(['data', $single_product]);
*/
        //$user = User::where('email', $request->email)->first();

        //$user->password = Hash::make($request->password);
        // $user->save();

        //return "ok";


        return "false";


        $name =   event(new SmsNotificationEvent('+256775496240', 'Hello mundruku, How are your?'));

        return $name;
        /*
        $email_object = new EmailNotification($request->name, $request->body);


        //send email notification to the user 
        Mail::to($request->email)->send($email_object);

        //Store notification in the database 

        Notification::create(
            [
                'body' => $request->body,
                'user_id' => $request->user_id,

            ]
        );
        */
    }


    public function saveDeviceToken(Request $request)
    {
        $token = $request->token;
        $id = auth()->user()->id;
        $user = User::find($id);
        if ($user) {
            $user->device_token = $token;
            $user->save();

            return response()->json(['result' => true]);
        }

        return response()->json(['result' => true]);
    }


    public  function checkAppVersion(Request $request)
    {


        if ($request->local == '2.9.1' && $request->remote == '2.9.3') {
            return response()->json(['result' => 1]);
        }


        return response()->json(['result' => 0]);
    }



    public function categoryAndSubCategoryUpdate(Request $request)
    {

        // return "Stup";



        $fcm = new FcmNotification();

        $fcmObject = (object) array(
            'token' => 'ejbo31V4QVeH2jK43tBMXv:APA91bEcppAI3dfg9OZnwgzPBsPlhRp3DJfSzcIrqWCGw_g7G-1_v4OK52pVOO7oMzFrLLGmktJOcHRbYCwY0O4IR_l9c2VWC8bXZLkFOKZpW3sCr4N8zCUBn7M8LPvnbX5RLWao4AYI',
            'body' => 'Testing message',
            'title' => 'New Message',
            'data' => []
        );
        $fcm->MessageToDevice($fcmObject);


        return "Ok";



        $email_object = new EmailNotification("mundruku Ivan", 'best message  received by Ivan');

        Mail::to('ivanmundruku@gmail.com')->send($email_object);

        return "OK";


        $notify = new FcmNotification();


        //return  $notify->MessageToDevice();

        //event(new SmsNotificationEvent('256775496240', 'Mundruku dont be stupid'));
        return "ok";

        $name = $request->name;
        $new_name = $request->new_name;

        //create a category

        $category = category::where('category_name', $name)->first();


        if ($category) {

            //create the new category
            if (!$request->id) {
                $cate = category::create([
                    'category_name' => $new_name,
                    'picture_url' => $category->picture_url,
                    'description' => $request->description
                ]);
            }

            //create the sub category
            $category_id = $request->id ? $request->id : $cate->id;



            $sub = subCategory::create(["subcat_name" => $category->category_name, "category_id" => $category_id]);


            //update product category id with recent category and sub category
            product::where('category_id', $category->id)->update([
                'category_id' => $category_id,
                'sub_category_id' => $sub->id

            ]);

            //update the banner galleries 

            if (!$request->id) {

                CategoryBanner::where('category_id', $category->id)->update(['category_id' => $category_id]);
            }
            //update banner gallries

            BannerGallary::where('category_id', $category->id)->update(['category_id' => $category_id]);

            $category->delete();

            return "OK";
        }

        return "failed";
    }




    public function top_suppliers(Request $request)
    {


        DB::statement("SET SQL_MODE=''");

        // $products = Sellers::join('users', 'sellers.seller_id', '=', 'users.id')
        //     ->where('users.account_types', 'farmer')
        //     // ->groupBy('products.user_id')
        //     ->select(
        //         'users.id',
        //         'sellers.b_name as full_name',
        //         'users.email',
        //         'users.created_at'
        //     )
        //     ->paginate(20);

        // foreach ($products as $product) {

        //     $product->prod = product::where('user_id', $product->id)
        //         ->where('is_approved', 1)
        //         ->groupBy('user_id')
        //         ->orderBy(DB::raw('count(user_id)'), 'desc')
        //         ->take(3)

        //         ->get();
        // }
        
        //returns products supplied by farmer according to volume submitted
        $products = product::orderBy(DB::raw('count(user_id)'), 'desc')->get();

        foreach ($products as $product) {

        //returns seller
        $seller = Sellers::join('users', 'sellers.seller_id', '=', 'users.id')
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

        return response()->json([
            'status' => true,
            'stores' => $seller
        ]);



        
    }


    public function profile_notification_count(){
        $id=auth()->user()->id;
    
        $message_count=Message::where('receiver_id', $id)
        ->where('seen_at', null)->count();

        $inquiry_count=ProductInquiry::where('user_id', $id)->count();

        return response()->json(['message'=>$message_count, 'inquiry'=>$inquiry_count]);


    }
}
