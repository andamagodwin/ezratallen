<?php

namespace App\Http\Controllers;

use App\BannerGallary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\category;
use App\CategoryBanner;
use App\Console\Commands\NewsFeedEmail;
use App\ContactTrecking;
use App\subCategory;
use App\region;
use App\district;
use App\product;
use App\Setting;
use App\Page;
use App\Country;
use App\EmailLogs;
use App\Events\ProductAddEvent;
use App\NewsLetterEmail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Image;
use App\Exports\UsersExport;
use App\Follow;
use App\Helper\FcmNotification;
use App\Helper\ProductHelper;
use App\HomeBanner;
use App\Mail\ProductApproved;
use App\PageContent;
use App\ProductGallery;
use App\ProductOrder;
use App\Rating;
use App\WishList;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{



    public function deleteUser($id)
    {


        $products = product::where('user_id', $id)->get();


        foreach ($products as $pro) {

            $product = product::find($pro->id);

            if ($product) {
                //get existing image path
                $image_path = public_path("storage/{$product->picture}");

                //Delete existing image in the file system
                if ($product->picture && File::exists($image_path)) {
                    unlink($image_path);
                }

                //delete product

                $product->delete();
            }
        }

        $user = User::find($id);
        if ($user) {
            product::where('user_id', $id)->delete();

            $user->delete();

            return response()->json(['result' => true]);
        }
    }

    public function getPendingProduct()
    {

        $product = product::join('users', 'users.id', '=', 'products.user_id')
            ->where('is_approved', 0)
            /*
        ->whereMonth('products.created_at', $request->month)
        ->whereYear('products.created_at', $request->year)
        */
            ->select(
                'users.full_name',
                'products.available_quantity',
                'products.view_count',
                'products.id',
                'products.product_title',
                'products.picture',
                'products.currency'
            )
            ->paginate(25);
        //$product = product::where('is_approved', 0)->get();

        return response()->json(['item' => $product]);
    }


    public function getUserProduct($id)
    {
        $product = product::where('user_id', $id)->get();

        return response()->json(['product' => $product]);
    }
    //return popular product to the admin api
    public function get_seller_products_by_popular()
    {
        $user_id = auth()->user()->id;
        $seller = product::where('user_id', $user_id)
                            ->groupBy('view_count')
                            ->orderBy(DB::raw('count(view_count)'), 'desc')
                            ->paginate(15);

        return response()->json(['result' => true, 'data' => $seller]);
    }

    public function rejectProduct($id)
    {

        $product = product::find($id);

        if ($product) {

            $product->is_approved = 2;

            $product->save();

            $user = User::find($product->user_id);

            $body =  $user->full_name . " Your product has being rejected. ";


            event(new ProductAddEvent($user->email, $user->full_name, $body, $product->product_title));
            //send to the user 
            // event(new ProductAddEvent($user->email, $user->full_name, $body));


            return response()->json(['result' => true]);
        }
    }


    public function approveProduct($id)
    {

        $product = product::find($id);

        if ($product) {

            $product->is_approved = 1;

            $product->save();

            $user = User::find($product->user_id);

            $body =  $user->full_name . " Your product has being approved. ";




            $followers = Follow::join('users', 'users.id', 'follows.follower_id')->where('user_id', $product->user_id)->get();


            foreach ($followers as $c) {

                //Send robust push notification 

                $body = "$c->full_name has uploaded a new product";

                if ($c->device_token) {

                    $fcm = new FcmNotification();



                    $fcmObject = (object) array(
                        'token' => $c->device_token,
                        'body' => $body,
                        'title' => 'New Product',
                        'data' => []
                    );
                    $fcm->MessageToDevice($fcmObject);
                }

                $notication = new ProductHelper();


                //send bell notification

                $object = (object) array(
                    'product_id' => $product->id,

                );

                $notication->NotificationHelper($c->follower_id, $body, $body, 'Product', json_encode($object), $user->id, $product->id);

                //Send Email notification


            }



            //send to the user 

            if ($user->email) {

                $url = 'https://farmsell.org/ViewProduct/' . $id;

                $email = new ProductApproved($user->full_name, $url, $user->product_title);

                Mail::to($user->email)->send($email);
                // event(new ProductAddEvent($user->email, $user->full_name, $body));
            }


            return response()->json(['result' => true]);
        }
    }

    public function deleteProduct($id)
    {

        $product = product::find($id);
        if ($product) {
            //product::where('user_id', $id)->delete();

            $product->delete();

            return response()->json(['result' => true]);
        }
    }

    //category, subcategory, region and ditrict add functions


    public function add_category(Request $request)
    {


        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'category_name' => 'required',
            //'description' => 'required',


        ]);



        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }



        if ($request->id) {

            $category = category::find($request->id);
            $category->category_name = $request->category_name;
            $category->description = $request->description;



            if ($request->image) {

                $fileName = md5(microtime()) . '_category.' . $request->image->getClientOriginalExtension();
                $image_path =  public_path("storage/banner/" . $fileName);

                Image::make($request->image)->save($image_path);

                $banner = CategoryBanner::where('category_id', $category->id)->first();

                if ($banner) {
                    $banner->picture_url = $fileName;
                    $banner->save();
                } else {
                    //add the category banner 
                    CategoryBanner::create([
                        'category_id' => $category->id,
                        'picture_url' => $fileName

                    ]);
                }
                //$category->save();
            }

            $category->save();
        } else {
            $category = new category();
            $category->category_name = $request->category_name;
            $category->description = $request->description;
            //$category->picture_url = $fileName;
            $category->save();



            //saving an array of pictures uploaded 

            if ($request->image) {

                $fileName = md5(microtime()) . '_category.' . $request->image->getClientOriginalExtension();
                $image_path =  public_path("storage/banner/" . $fileName);

                Image::make($request->image)->save($image_path);


                //add the category banner 
                CategoryBanner::create([
                    'category_id' => $category->id,
                    'picture_url' => $fileName

                ]);

                //$category->picture_url = $fileName;

                //$category->save();
            }
        }



        /*
        if ($request->length)
            for ($i = 0; $i < $request->length; $i++) {
                $file_name = 'file' . $i;
                $fileName = md5(microtime()) . '_category.' . $request->$file_name->getClientOriginalExtension();
                $image_path =  public_path("storage/banner/" . $fileName);

                Image::make($request->$file_name)->save($image_path);

                if ($i == 0) {
                    $category_find = category::find($category->id);
                    $category_find->picture_url = $fileName;
                    $category_find->save();
                }
                $gallary = new BannerGallary();
                $gallary->category_id = $category->id;
                $gallary->banner_url = $fileName;
                $gallary->save();
            }
            */




        return response()->json(['category' => $category, 'success' => "Category created succesfully"]);
    }





    public function add_subcategory(Request $request)
    {



        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'sub_product' => 'required',
            'category_id' => 'required',

        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $sub = json_decode($request->sub_product);
        $id = $request->category_id;

        // return response()->json(['d'=>json_decode($sub)]);

        //remove exiisting sub category from the db;

        foreach ($sub as $item) {

            if (!$item->id) {

                subCategory::create(

                    ["subcat_name" => $item->name, "category_id" => $id]
                );
            }
        }


        return response()->json(['result' => true, 'success' => "subcategory created succesfully"]);
    }



    public function subcategory_delete(Request $request, $id)
    {

        $subcategory_delete = subCategory::find($id);

        if ($subcategory_delete) {

            $subcategory_delete->delete();

            return response()->json(['result' => true]);
        }
    }

    public function getSubCategories($id)
    {

        $sub = subCategory::where('category_id', $id)

            ->select('sub_categories.id', 'sub_categories.subcat_name as name')
            ->get();
        $category = category::find($id);


        return response()->json(['data' => $sub, 'name' => $category->category_name]);
    }






    public function add_region(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'region_name' => 'required',

        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $region = region::create($request->all());
        return response()->json(['region' => $region, 'success' => "Region created succesfully"]);
    }

    public function getUsers(Request $request)
    {



        if ($request->all) {

            $count = User::count();

            $user = User::where('user_type', 'user')
                ->orderBy('id', 'desc')
                ->paginate($count);

            return response()->json(['user' => $user]);
        }


        if ($request->month && $request->year) {
            $user = User::where('user_type', 'user')

                ->whereMonth('created_at', $request->month)

                ->whereYear('created_at', $request->year)
                ->orderBy('id', 'desc')

                ->paginate($request->show_entries);

            return response()->json(['user' => $user]);
        }

        if ($request->year) {

            $user = User::where('user_type', 'user')

                ->whereYear('created_at', $request->year)
                ->orderBy('id', 'desc')

                ->paginate($request->show_entries);

            return response()->json(['user' => $user]);
        }

        if ($request->today) {

            $user = User::where('user_type', 'user')

                ->whereDay('created_at', $request->today)
                ->orderBy('id', 'desc')

                ->paginate($request->show_entries);

            return response()->json(['user' => $user]);
        }

        if ($request->online) {

            $user = User::where('user_type', 'user')

                ->whereTime('updated_at', $request->online)
                ->orderBy('id', 'desc')

                ->paginate($request->show_entries);

            return response()->json(['user' => $user]);
        }
    }






    public function searchUser(Request $request)
    {
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

        if ($request->user) {
            $users = User::where('full_name', 'LIKE', '%' . $user_name . '%')
                ->where('user_type', 'user')
                ->get();
        }

        if ($request->admin) {
            $users = User::where('full_name', 'LIKE', '%' . $user_name . '%')
                ->where('user_type', 'admin')
                ->get();
        }

        return response()->json(['user' => $users]);
    }



    public function searchProduct(Request $request)
    {
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

        $users = product::where('product_title', 'LIKE', '%' . $user_name . '%')
            // ->where('user_type', 'user')
            ->where('is_approved', 1)
            ->get();




        return response()->json(['user' => $users]);
    }


    public function getAdmins()
    {
        $user = User::where('user_type', '!=', 'user')
            ->paginate(15);

        return response()->json(['user' => $user]);
    }




    public function add_district(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'district_name' => 'required',
            'region_id' => 'required',

        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $district = district::create($request->all());
        return response()->json(['ditrict' => $district, 'success' => "District created succesfully"]);
    }


    // category, subcategory, region and district  list view functions 

    public function category_view(Request $request)
    {

        $category_view_list = category::join(
            'category_banners',
            'category_banners.category_id',
            '=',
            'categories.id'

        )->get();


        return response()->json(['category_view_list' => $category_view_list]);
    }




    public function getCategoryDetails($id)
    {
        $category = category::find($id);
        $picture = CategoryBanner::where('category_id', $id)->first();


        $category->picture_url = $picture->picture_url;

        $product_count = product::where('category_id', $id)
            ->where('is_approved', 1)
            ->count();
        $category->product = $product_count;

        return response()->json(['result' => true, 'data' => $category]);
    }


    public function subcategory_view(Request $request)
    {

        $subcategory_view_list = subCategory::leftjoin('categories', 'categories.id', '=', 'sub_categories.category_id')->select(
            'categories.category_name',
            'sub_categories.subcat_name',
            'sub_categories.category_id',
            'sub_categories.id',
            'sub_categories.created_at',
            'sub_categories.updated_at'
        )->get();;

        return response()->json(['category_view_list' => $subcategory_view_list]);
    }


    public function region_view(Request $request)
    {

        $region_view_list = region::all();

        return response()->json(['category_view_list' => $region_view_list]);
    }

    public function ViewNewsLettersEmails(Request $request)
    {

        $NewsLettersEmails = NewsLetterEmail::all();

        return response()->json(['NewsLettersEmails' => $NewsLettersEmails]);
    }

    public function district_view(Request $request)
    {

        $district_view_list = district::leftjoin('regions', 'regions.id', '=', 'districts.region_id')->select(
            'regions.region_name',
            'districts.district_name',
            'districts.region_id',
            'districts.id',
            'districts.created_at',
            'districts.updated_at'
        )->get();


        return response()->json(['category_view_list' => $district_view_list]);
    }


    // category, subcategory, region and district  delete functions


    public function category_delete(Request $request, $id)
    {

        $category = category::find($id);


        if ($category) {

            //  product::where('category_id', $id)->delete();
            subCategory::where('category_id', $id)->delete();

            $category->delete();

            //$subcategory = subCategory::where('category_id', $id)->first();
            BannerGallary::where('category_id', $id)->delete();
            /*
            if ($subcategory) {

                $subcategory->delete();

                return response()->json(['success' => 'Category and Subcategory Deleted  successfully']);
            }
            */

            return response()->json(['success' => 'Category Deleted  successfully']);
        }
    }




    public function region_delete(Request $request, $id)
    {

        $region = region::find($id);


        if ($region) {

            $region->delete();

            $district = district::where('region_id', $id)->first();

            if ($district) {

                $district->delete();

                return response()->json(['success' => 'region and its associated district Deleted  successfully']);
            }

            return response()->json(['success' => 'region Deleted  successfully']);
        }
    }



    public function district_delete(Request $request, $id)
    {

        $district_delete = district::find($id);

        if ($district_delete) {

            $district_delete->delete();
            return response()->json(['success' => 'Ditrict deleted succesfully']);
        }
    }



    // category, subcategory, region and district  edit functions


    public function category_edit(Request $request, $id)
    {


        $category = category::find($id);

        if ($request->has('description') && $category) {
            $category->description = $request->description;
        }


        /*
        if($request->file_to_send){
            
            
                            $fileName = md5(microtime()) . '_category.webp';

                $image_path =  public_path("storage/banner/" . $fileName);
                
            
                 Storage::disk('public')->put('bannner/'.$fileName, File::get($request->file_to_send));
                
                
                return $fileName;
            
        }
        */


        //saving an array of pictures uploaded 

        if ($request->length) {

            for ($i = 0; $i < $request->length; $i++) {
                $file_name = 'file' . $i;

                $fileName = md5(microtime()) . '_category.webp';

                $image_path =  public_path("storage/banner/" . $fileName);

                Image::make($request->$file_name)->encode('webp')->save($image_path);

                if ($i == 0 && $category->picture_url == null) {

                    $category->picture_url = $fileName;
                }

                $gallary = new BannerGallary();
                $gallary->category_id = $category->id;
                $gallary->banner_url = $fileName;
                $gallary->save();
            }
        }



        $category->save();
        return response()->json(['success' => 'Editted succesfully']);
    }


    public function getCategoryBanner($id)
    {

        $banner = BannerGallary::where('category_id', $id)->get();

        return response()->json(['banner' => $banner]);
    }


    public function addCategoryBanner(Request $request)
    {


        if ($request->length) {

            for ($i = 0; $i < $request->length; $i++) {
                $file_name = 'file' . $i;

                $fileName = md5(microtime()) . '_category.webp';

                $image_path =  public_path("storage/banner/" . $fileName);

                Image::make($request->$file_name)->save($image_path);

                $gallary = new BannerGallary();
                $gallary->category_id = $request->id;
                $gallary->banner_url = $fileName;
                $gallary->save();
            }
        }


        return response()->json(['result' => true]);
    }


    //delete exixsting category image 

    public function delete_exisiting_category_image($id)
    {
        $image = BannerGallary::find($id);

        if ($image) {


            $image_path = public_path("storage/banner/{$image->banner_url}");

            //Delete existing image in the file system
            if ($image->banner_url && File::exists($image_path)) {
                unlink($image_path);
            }
            $image->delete();

            return response()->json(['result' => true]);
        }
    }


    public function subcategory_edit(Request $request, $id)
    {

        $subcategory = subCategory::find($id);

        if ($request->has('subcat_name') && $subcategory) {
            $subcategory->subcat_name = $request->subcat_name;

            $subcategory->save();
            return response()->json(['success' => 'Editted succesfully']);
        }


        return response()->json(['error' => 'Subcategory not found in the database']);
    }


    public function region_edit(Request $request, $id)
    {

        $region = region::find($id);

        if ($request->has('region_name') && $region) {
            $region->region_name = $request->region_name;

            $region->save();
            return response()->json(['success' => 'Editted succesfully']);
        }



        return response()->json(['error' => 'Region not found in the database']);
    }



    public function district_edit(Request $request, $id)
    {

        $district = district::find($id);

        if ($request->has('district_name') && $district) {
            $district->district_name = $request->district_name;

            $district->save();
            return response()->json(['success' => 'Editted succesfully']);
        }



        return response()->json(['error' => 'District not found in the database']);
    }


    public function verifyUser($id)
    {
        $user = User::find($id);

        if ($user) {
            $user->verification_status = $user->verification_status == 1 ? 0 : 1;

            $user->save();
        }

        if ($user->verification_status == 1) {
            $body = 'Your Account has been approved. ';
        } else {
            $body = 'Your Account has been approved. ';
        }

        //send to the user 
        // event(new ProductAddEvent($user->email, $user->full_name, $body));

        return response()->json(['user' => $user]);
    }
    public function getUser($id)
    {
        $user = User::find($id);

        $following_count = Follow::where('user_id', $id)->count();
        $followers_count = Follow::where('follower_id', $id)->count();
        $product = product::where('user_id', $id)->count();

        if ($user) {
            $user->following = $following_count;
            $user->followers = $followers_count;
            $user->created = Carbon::parse($user->created_at)->format('Y-m-d');
            $user->updated = Carbon::parse($user->updated_at)->format('Y-m-d');
            $user->product = $product;
        }

        return response()->json(['data' => $user]);
    }

    public function users_count(Request $request)
    {

        $provider = product::distinct()->count(['user_id']);

        $users = User::where('user_type', 'user')->count();

        $product = product::all()->count();
        $approved = product::where('is_approved', 1)->count();
        $pending = product::where('is_approved', 0)->count();
        $rejected = product::where('is_approved', 2)->count();
        $closed = product::where('is_approved', 3)->count();

        $orders = ProductOrder::count();




        $users_latest = User::orderBy('id', 'desc')->paginate(10);

        $providers = product::join('users', 'users.id', '=', 'products.user_id')
            ->select(
                'users.full_name',
                'products.created_at',
                'users.id',
                'users.picture',
                'users.avatar_google'
            )
            ->orderBy('products.id', 'desc')
            ->paginate(10);


        $product_latest = product::where('is_approved', 1)
            ->orderBy('id', 'desc')->paginate(10);


        return response()->json([
            'product' => $product,
            'provider' => $provider,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'closed' => $closed,
            'order' => $orders,
            'user_provider' => $providers,
            'user' => $users, 'latest_user' => $users_latest, 'latest_product' => $product_latest
        ]);
    }

    public function latest_users_products(Request $request)
    {


        //return response()->json(['product' => $product, 'user' => $users]);
    }


    //site settings 

    public function save_settings(Request $request)
    {

        $path_site_logo = null;
        $path_site_footer = null;
        $path_site_fav = null;

        if ($request->id) {

            $setting = Setting::find($request->id);
            $setting->site_name = $request->site_name ?: $setting->site_name;
            $setting->phone = $request->phone ?: $setting->phone;
            $setting->email = $request->email ?: $setting->email;
            $setting->facebook = $request->facebook ?: $setting->facebook;
            $setting->youtube = $request->youtube ?: $setting->youtube;
            $setting->linkedin = $request->linkedin ?: $setting->linkedin;
            $setting->twitter = $request->twitter ?: $setting->twitter;
            $setting->site_domain = $request->site_domain ?: $setting->site_domain;

            if ($setting->site_logo && $request->hasFile('site_logo')) {
                $image_path = public_path("storage/settings/{$setting->site_logo}");

                if (File::exists($image_path)) {
                    unlink($image_path);
                }
                $fileName = md5(microtime()) . '_logo.webp';
                $image_path =  public_path("storage/settings/" . $fileName);
                Image::make($request->site_logo)->save($image_path);
                $path_site_logo = $fileName;
            }


            if ($setting->site_footer_logo && $request->hasFile('site_footer_logo')) {
                $image_path = public_path("storage/settings/{$setting->site_logo}");

                if (File::exists($image_path)) {
                    unlink($image_path);
                }
                $fileName = md5(microtime()) . '_logo.webp';
                $image_path =  public_path("storage/settings/" . $fileName);
                Image::make($request->site_footer_logo)->save($image_path);
                $path_site_footer = $fileName;
            }

            if ($setting->site_footer_fav && $request->hasFile('site_footer_fav')) {
                $image_path = public_path("storage/settings/{$setting->site_logo}");

                if (File::exists($image_path)) {
                    unlink($image_path);
                }
                $fileName = md5(microtime()) . '_logo.webp';
                $image_path =  public_path("storage/settings/" . $fileName);
                Image::make($request->site_footer_fav)->save($image_path);
                $path_site_footer = $fileName;
            }

            $setting->site_logo = $path_site_logo ?: $setting->site_logo;
            $setting->site_logo_footer = $path_site_footer ?: $setting->site_footer_logo;
            $setting->site_logo_fav = $path_site_fav ?: $setting->site_footer_fav;

            if ($setting->save()) {
                return response()->json(['result' => 'succesfully edited site settings', 'setting' => $setting]);
            }
        } else {
            $setting = new Setting();
            $setting->site_name = $request->site_name ?: null;
            $setting->phone = $request->phone ?: null;
            $setting->email = $request->email ?: null;
            $setting->facebook = $request->facebook ?: null;
            // $setting->location = $request->location ?: null;
            $setting->youtube = $request->youtube ?: null;
            $setting->twitter = $request->twitter ?: null;
            $setting->linkedin = $request->linkedin ?: null;
            $setting->site_domain = $request->site_domain ?: null;

            if ($request->hasFile('site_logo')) {
                $fileName = md5(microtime()) . '_logo.webp';
                $image_path =  public_path("storage/settings/" . $fileName);
                Image::make($request->site_logo)->save($image_path);
                $path_site_logo = $fileName;
            }

            if ($request->hasFile('site_logo_footer')) {
                $fileName = md5(microtime()) . '_logo.webp';
                $image_path =  public_path("storage/settings/" . $fileName);
                Image::make($request->site_logo_footer)->save($image_path);
                $path_site_footer = $fileName;
            }

            if ($request->hasFile('site_logo_fav')) {
                $fileName = md5(microtime()) . '_logo.webp';
                $image_path =  public_path("storage/settings/" . $fileName);
                Image::make($request->site_logo_fav)->save($image_path);
                $path_site_fav = $fileName;
            }

            $setting->site_logo = $path_site_logo;
            $setting->site_logo_footer = $path_site_footer;
            $setting->site_logo_fav = $path_site_fav;

            if ($setting->save()) {
                return response()->json(['result' => 'succesfully edited site settings', 'setting' => $setting]);
            }
        }
    }

    //get site settings

    public function get_settings(Request $request)
    {

        $setting = Setting::all();

        return response()->json(['setting' => $setting]);
    }


    //Save site pages function

    public  function save_page(Request $request)
    {


        $page_exist = Page::where('get_status', 1)->first();

        if ($page_exist) {

            $page = $page_exist;

            $page->about_us = $request->about_us ?: $page->about_us;
            $page->about_us_summary = $request->about_us_summary ?: $page->about_us_summary;
            $page->privacy_policy = $request->privacy_policy ?: $page->privacy_policy;
            $page->cookie_policy = $request->cookie_policy ?: $page->cookie_policy;
            $page->terms_condition = $request->terms_condition ?: $page->terms_condition;
            $page->help_support = $request->help_support ?: $page->help_support;

            if ($page->save()) {
                return response()->json(['page' => $page]);
            }
        } else {
            $page = new Page();

            $page->get_status = 1;
            $page->about_us = $request->about_us ?: null;
            $page->about_us_summary = $request->about_us_summary ?: null;
            $page->privacy_policy = $request->privacy_policy ?: null;
            $page->cookie_policy = $request->cookie_policy ?: null;
            $page->terms_condition = $request->terms_condition ?: null;
            $page->help_support = $request->help_support ?: null;


            if ($page->save()) {
                return response()->json(['page' => $page]);
            }
        }
    }




    //Country Add implimentation

    public function add_country(Request $request)
    {
        if ($request->country_name && $request->picture) {

            $fileName = md5(microtime()) . '_country.webp';
            $image_path =  public_path("storage/country/" . $fileName);
            Image::make($request->file('picture'))->resize(320, 240)->encode('webp')->save($image_path);


            $country = new Country();
            $country->country_name = $request->country_name;
            $country->photo_url = $fileName;
            $country->save();
            return response()->json(['message' => 'Succesfully added country']);
        }


        return response()->json(['error' => 'Country name is required']);
    }

    public function edit_country(Request $request)
    {
        $country = Country::find($request->id);

        if ($request->country_name && $country) {

            $country->country_name = $request->country_name;
        }

        if ($request->has('picture') && $country) {
            $image_path = public_path("storage/country/{$country->photo_url}");

            //Delete existing image in the file system
            if ($country->photo_url && File::exists($image_path)) {
                unlink($image_path);
            }

            $fileName = md5(microtime()) . '_country.webp';

            $image_path2 =  public_path("storage/country/" . $fileName);
            Image::make($request->picture)->encode('webp')->save($image_path2);

            $country->photo_url = $fileName;
        }

        $country->save();
        return response()->json(['message' => 'Succesfully added country']);
    }

    public function getProductSummary(Request $request)
    {

        $product = product::join('users', 'users.id', '=', 'products.user_id')
            ->where('products.is_approved', $request->id)
            ->select('products.product_title', 'users.full_name', 'products.picture', 'products.view_count', 'products.currency', 'products.created_at as uploaded', 'products.id', 'products.address', 'users.country_code')
            ->orderBy('products.id', 'desc')
            ->paginate($request->count);

        return response()->json(['data' => $product]);
    }


    public function deleteProductInBulk(Request $request)
    {


        //return response()->json(['items', is_array(json_decode($request->items))]);
        if (is_array(json_decode($request->items))) {
            foreach (json_decode($request->items) as $c) {
                $product = product::find($c);
                if ($product) {

                    $product->delete();
                }
            }
        }

        return response()->json(['result' => true]);
    }


    public  function getProducts(Request $request)
    {




        if ($request->month && $request->year) {

            $product = product::join('users', 'users.id', '=', 'products.user_id')
                ->where('is_approved', 1)
                ->whereMonth('products.created_at', $request->month)
                ->whereYear('products.created_at', $request->year)
                ->select(
                    'users.full_name',
                    'products.available_quantity',
                    'products.view_count',
                    'products.id',
                    'products.address',
                    'products.created_at as uploaded',
                    'products.product_title',
                    'products.picture',
                    'products.created_at as uploaded',
                    'products.currency'
                )
                ->paginate($request->show_entries);

            return response()->json(['product' => $product]);
        }

        if ($request->year) {


            $product = product::join('users', 'users.id', '=', 'products.user_id')
                ->whereYear('products.created_at', $request->year)
                ->where('is_approved', 1)
                ->select(
                    'users.full_name',
                    'products.available_quantity',
                    'products.view_count',
                    'products.id',
                    'products.product_title',
                    'products.picture',
                    'products.currency',
                    'products.address',
                    'products.created_at as uploaded',
                )
                ->paginate($request->show_entries);

            return response()->json(['product' => $product]);
        }

        if ($request->today) {

            $product = product::join('users', 'users.id', '=', 'products.user_id')
                ->whereDay('products.created_at', $request->today)
                ->where('is_approved', 1)
                ->select(
                    'users.full_name',
                    'products.available_quantity',
                    'products.view_count',
                    'products.id',
                    'products.product_title',
                    'products.picture',
                    'products.currency',
                    'products.address',
                    'products.created_at as uploaded',
                )
                ->paginate($request->show_entries);

            return response()->json(['product' => $product]);
        }

        if ($request->online) {

            $product = product::join('users', 'users.id', '=', 'products.user_id')
                ->whereTime('products.updated_at', $request->online)
                ->where('is_approved', 1)
                ->select(
                    'users.full_name',
                    'products.available_quantity',
                    'products.view_count',
                    'products.id',
                    'products.product_title',
                    'products.picture',
                    'products.currency',

                )
                ->paginate($request->show_entries);

            return response()->json(['product' => $product]);
        }
    }


    //change catgory records 


    public function changeCategory(Request $request)
    {

        $product = product::find($request->product_id);

        if ($product) {
            $product->category_id = $request->category_id;
            $product->save();

            return response()->json(['result' => true]);
        }
    }

    public function productView($id)
    {
        $product = product::find($id);

        $product_count = product::where('user_id', $product->user_id)->count();


        //$followers_count = Follow::where('follower_id', $product->user_id)->count();
        // $find_wishlist = WishList::where('product_id', $id)->where('user_id', $request->viewer_id)->first();

        // $favorite_status = false;





        $single_product = product::join('users', 'users.id', '=', 'products.user_id')
            // ->join('districts', 'districts.id', '=', 'products.district_id')
            ->where('products.id', $id)->select(
                'products.product_title',
                'products.price',
                'products.id as product_id',
                'products.is_approved',
                'products.picture',
                'products.user_id',
                'products.description',
                'products.created_at',
                'products.category_id',
                'users.id',
                'users.full_name',
                'products.longitude',
                'products.latitude',
                'products.address',
                'products.available_quantity',
                'products.units',
                'users.phone as phone',
                'users.email',
                'users.picture as photo',
                'users.avatar_google',
                'users.country_code',
                'users.updated_at',
                'products.view_count',
                'products.currency'
            )->get();

        $product_details = [];
        $rating = 0;

        foreach ($single_product as $product) {



            $rating_count = Rating::where('product_id', $id)->count();
            $ratings = Rating::where('product_id', $id)->get();

            $category = category::find($product->category_id);
            if ($category) {
                $product->catgory_name = $category->category_name;
            }

            foreach ($ratings as $rate) {
                $rating = $rating + $rate->rating;
            }

            if ($rating != 0)
                $rating = $rating / $rating_count;


            $product->rating = $rating;

            $pro = product::find($id);

            //get the logged in user and check with ProductViewLog



            $string  = $single_product[0]->updated_at->diffForHumans();
            $string = trim(preg_replace('!\s+!', ' ', $string));
            $array_of_words = explode(" ", $string);
            $number = (int)$array_of_words[0];
            $seconds = $array_of_words[1];


            if ($number < 59 && ($seconds == 'seconds' || $seconds == 'second')) {
                $product->online_status = 'online';
            } else {
                $product->online_status = $single_product[0]->updated_at->diffForHumans();
            }



            //$product->product_create_date = Carbon::parse($product->product_create_date)->diffForHumans();
            //$product->favorite_status = $favorite_status;

            array_push($product_details, $product);
        }

        //$comment_count = Comment::where('product_id', $id)->count();

        //$rating_data = $this->product_rating_records($id, $request->user_id);



        $gallery = ProductGallery::where('product_id', $id)->get();

        if ($single_product) {

            return response()->json([
                'details' => $product_details,

                'product_count' => $product_count,
                /*
                'related_product' => $relatedArray,
                'rating_data' => $rating_data,
                'comment' => $comment_data,
                */
                'gallery' => $gallery,
                'categories' => category::all()
            ]);
        }
    }


    public function ExportExcell(Request $request)
    {


        return Excel::download(new UsersExport, 'users.xlsx');
    }


    public function storeSiteInformation(Request $request)
    {

        //if request has id then edit existing 

        if ($request->id) {




            return;
        }
    }


    public function AddMainBanner(Request $request)
    {


        if ($request->hasFile('picture')) {


            $fileName = md5(microtime()) . '_banner.webp';

            $image_path =  public_path("storage/banner/" . $fileName);

            Image::make($request->picture)->save($image_path);

            $gallary = new HomeBanner();

            $gallary->url = $fileName;

            $gallary->save();

            return response()->json(['result' => true]);
        }
    }

    public function deleteMainBanner($id)
    {

        $banner = HomeBanner::find($id);

        if ($banner) {
            $banner->delete();

            return response()->json(['result' => true]);
        }

        return response()->json(['result' => false]);
    }

    public function GetMainBanner()
    {

        $banner = HomeBanner::all();

        return response()->json(['banner' => $banner]);
    }



    //Save page 
    public function addPage(Request $request)
    {


        Page::create([
            'name' => $request->name
        ]);


        return response()->json(['result' => true]);
    }



    //get page
    public function getPage()
    {
        $page = Page::all();
        if ($page) {
            return response()->json(['page' => $page]);
        }

        return response()->json(['error' => 'No page  found, please add page required']);
    }


    //add page contents 

    public function addPageContent(Request $request)
    {



        if ($request->id) {
            $content = PageContent::find($request->id);
            if ($content) {

                $content->title = $request->title;
                $content->page_type = $request->page_type;
                $content->page_content = $request->page_content;
                $content->save();
            }

            return response()->json(['result' => true]);
        }

        PageContent::create([
            'title' => $request->title,
            'page_id' => $request->page_id,
            'page_type' => $request->page_type,
            'page_content' => $request->page_content
        ]);


        return response()->json(['result' => true]);
    }



    //add page contents 

    public function getPageContent($id)
    {


        $page = Page::join('page_contents', 'page_contents.page_id', '=', 'pages.id')
            ->where('page_contents.page_id', $id)
            ->select(
                'page_contents.title',
                'page_contents.id',
                'page_contents.page_id',
                'page_contents.page_type',
                'page_contents.page_content'
            )
            ->get();

        return response()->json(['page' => $page]);
    }

    //deleting specific page content 

    public  function deletePageContent($id)
    {
        $page_content = PageContent::find($id);

        if ($page_content) {
            $page_content->delete();
            return response()->json(['result' => true]);
        }
    }

    //deleting page added

    public  function deletePage($id)
    {
        $page = Page::find($id);

        if ($page) {

            $page->delete();
            PageContent::where('page_id', $id)->delete();
            return response()->json(['result' => true]);
        }
    }








    //function to get the statistics of the users and products on yearly basis

    public function userAndProductStatistics($year)
    {

        $users = User::select('id', 'created_at')
            ->whereYear('created_at', $year)
            ->get()
            ->groupBy(function ($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });

        $products = product::select('id', 'created_at')
            ->whereYear('created_at', $year)
            ->get()
            ->groupBy(function ($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });


        $usermcount = [];
        $productmcount = [];


        $userArr = [];

        $productArr = [];


        foreach ($users as $key => $value) {
            $usermcount[(int)$key] = count($value);
        }

        foreach ($products as $key => $value) {
            $productmcount[(int)$key] = count($value);
        }


        for ($i = 1; $i <= 12; $i++) {
            if (!empty($usermcount[$i])) {
                $userArr[$i] = $usermcount[$i];
            } else {
                $userArr[$i] = 0;
            }
        }

        for ($i = 1; $i <= 12; $i++) {
            if (!empty($productmcount[$i])) {
                $productArr[$i] = $productmcount[$i];
            } else {
                $productArr[$i] = 0;
            }
        }


        return response()->json(['pcount' => $productArr, 'ucount' => $userArr]);
    }


    public function sendNewsLetter(Request $request)
    {
        $title = $request->title;

        $body = $request->body;

        $news = new EmailLogs();

        $news->title = $title;
        $news->to = 'All the users';
        $news->body = $body;

        $news->save();

        return response()->json(['result' => true]);
    }


    public function getContactClicks()
    {

        $contacts = ContactTrecking::all();

        foreach ($contacts as $c) {
            $product = product::find($c->product_id);

            if ($c->user_id) {
                $user = User::find($c->user_id);
                if ($user) {
                    $c->name = $user->full_name;
                } else {
                    $c->name = 'Visitor';
                }
            } else {
                $c->name = 'Visitor';
            }
            if ($product) {
                $c->product = $product->product_title;
            }
        }

        return response()->json(['contact' => $contacts]);
    }

    public function getNewsLetter(Request $request)
    {

        $emails = EmailLogs::all();

        return response()->json(['email' => $emails]);
    }



    //getting orders 

    public function getOrders()
    {


        $orders = ProductOrder::orderBy('id', 'desc')
            ->paginate(20);

        /*
        foreach ($orders as $item) {
            $item->buyer = User::find($item->customer_id);
            $item->seller = User::find($item->seller_id);
            $item->product = product::find($item->product_id);
        }

        */
        return response()->json(
            [
                'result' => true,
                'orders' => $orders

            ]
        );
    }

    //getting order details

    public function getOrder($id)
    {

        $order = ProductOrder::find($id);

        if ($order) {

            $order->product = product::find($order->product_id);
            $order->buyer = User::find($order->customer_id);
            $order->seller = User::find($order->seller_id);
            $order->gallery = ProductGallery::where('product_id', $order->product_id)->get();
        }

        /*
        foreach ($orders as $item) {
            $item->buyer = User::find($item->customer_id);
            $item->seller = User::find($item->seller_id);
            $item->product = product::find($item->product_id);
        }

        */
        return response()->json(
            [
                'result' => true,
                'order' => $order

            ]
        );
    }

    public function details()
    {

        $product = product::all();


        foreach ($product as $c) {
            $user = User::find($c->user_id);
            if ($c->latitude) {

                if ($user) {
                    $user->registered_from = 1;
                    $user->save();
                }
            } else {
                if ($user) {
                    $user->registered_from = 2;
                    $user->save();
                }
            }
        }

        return "ok";
    }
}
