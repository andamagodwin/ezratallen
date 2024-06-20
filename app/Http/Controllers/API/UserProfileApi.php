<?php

namespace App\Http\Controllers\API;

use App\AcountDelete;
use App\Follow;
use App\Helper\FcmNotification;
use App\Helper\ProductHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\product;
use App\User;
use App\WishList;
use GuzzleHttp\Promise\Create;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Image;

class UserProfileApi extends Controller
{

    public  function profile_update(Request $request)
    {




        // find the user 
        $user = User::find($request->user_id);

        if ($request->full_name)
            $user->full_name = $request->full_name;

        if ($request->phone)
            $user->phone = $request->phone;

        if ($request->email)
            $user->email = $request->email;


        if ($request->user_name)
            $user->user_name = $request->user_name;

        if ($request->user_status)
            $user->user_status = $request->user_status;

        if ($request->about_user)
            $user->about_user = $request->about_user;

        if ($request->language)
            $user->language = $request->language;

        if ($request->site_url)
            $user->site_url = $request->site_url;

        if ($request->gender)
            $user->gender = $request->gender;

        if ($request->location)
            $user->location = $request->location;


        if ($request->country)
            $user->country = $request->country;





        if ($request->user_intention)
            $user->user_intention = $request->user_intention;

        if ($request->date_of_birth)
            $user->date_of_birth = $request->date_of_birth;

        if ($request->physical_address)
            $user->physical_address = $request->physical_address;

        if ($request->user_profile)
            $user->user_profile = $request->user_profile;

        if ($request->fb_username)
            $user->fb_username = $request->fb_username;

        if ($request->twitter_username)
            $user->twitter_username = $request->twitter_username;

        if ($request->linkedin_username)
            $user->linkedin_username = $request->linkedin_username;

        if ($request->youtube_username)
            $user->youtube_username = $request->youtube_username;

        if ($request->image)
            $this->picture_update($request);

        if ($request->image_cover)
            $this->cover_picture_update($request);


        $user->save();

        // find the user 
        $user = User::find($request->user_id);

        return response(['user' => $user, 'success' => 'you have succesfully editted your profile']);
    }


    //remove cover or profile picture
    public  function removeImage(Request $request)
    {
        // find the user 
        $user = User::find($request->user_id);



        if ($user) {

            //remove the profile picture

            if ($request->status == 1) {

                $image_path = public_path("storage/{$user->picture}");

                $user->picture = null;

                if ($user->picture && File::exists($image_path)) {
                    unlink($image_path);
                }
            }

            //remove the cover picture
            if ($request->status == 2) {

                //get existing image path
                $image_path = public_path("storage/{$user->image_cover}");


                //Delete existing image in the file system
                if ($user->image_cover && File::exists($image_path)) {
                    unlink($image_path);
                }
                $user->image_cover = null;
            }
            $user->save();

            return response(['user' => $user, 'success' => 'you have succesfully editted your profile']);
        }
    }

    //User profile picture update

    public function picture_update(Request $request)
    {

        // find the user 
        $user = User::find($request->user_id);


        if ($request->hasFile('image')) {

            //get existing image path
            $image_path = public_path("storage/{$user->picture}");


            //Delete existing image in the file system
            if ($user->picture && File::exists($image_path)) {
                unlink($image_path);
            }

            $fileName = md5(microtime()) . '_avatars.' . $request->image->getClientOriginalExtension();;

            $image_path =  public_path("storage/avatars/" . $fileName);

            Image::make($request->image)->resize(320, 240)->save($image_path);


            $path = 'avatars/' . $fileName;
        }
        //        $path = $request->file('image')->store('avatars', ['disk' => 'public']);


        if ($path) {

            //store the image url in database 

            $user->picture = $path;

            $user->save();

            //Return success response 
            return response(['url' => $path, 'success' => true, 'user' => $user]);
        } else {
            return response(['error' => "Failed to updated profile"]);
        }
    }

    //User profile picture update

    public function cover_picture_update(Request $request)
    {


        // find the user 
        $user = User::find($request->user_id);

        $path = null;


        if ($request->hasFile('image_cover')) {



            //get existing image path
            $image_path = public_path("storage/{$user->image_cover}");


            //Delete existing image in the file system
            if ($user->image_cover && File::exists($image_path)) {
                unlink($image_path);
            }




            $fileName = md5(microtime()) . '_cover.' . $request->image_cover->getClientOriginalExtension();

            $image_path =  public_path("storage/avatars/" . $fileName);
            Image::make($request->image_cover)->save($image_path);


            $path = 'avatars/' . $fileName;
        }
        //        $path = $request->file('image')->store('avatars', ['disk' => 'public']);


        if ($path) {

            //store the image url in database 

            $user->image_cover = $path;
            $user->save();


            //Return success response 
            return response(['url' => $path, 'success' => true, 'user' => $user]);
        } else {
            return response(['error' => "Failed to updated profile"]);
        }
    }




    //method for following a user 


    public function follow_user(Request $request)
    {

        /**Validate the data using validation rules
         */

        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',

            //  'follower_id' => 'required'
        ]);

        if ($request->user_id == $user->id) {
            return response()->json(['error' => 'Not allowed'], 403);
        }

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $check_follow_status = Follow::where('user_id', $request->user_id)
            ->where('follower_id', $user->id)
            ->first();

        if ($check_follow_status) {
            $check_follow_status->delete();
            return response()->json(['follow' => false]);
        }

        /**Store all values of the fields
         */


        $follow = new Follow();

        $follow->user_id = $request->user_id;
        $follow->follower_id = $user->id;
        $follow->save();


        $follower = auth()->user();

        $user = User::find($follow->user_id);


        $body = "$follower->full_name is following you";

        if ($user->device_token) {

            $fcm = new FcmNotification();



            $fcmObject = (object) array(
                'token' => $user->device_token,
                'body' => $body,
                'title' => 'New Follower',
                'data' => []
            );
            $fcm->MessageToDevice($fcmObject);
        }

        $notication = new ProductHelper();


        //send bell notification

        $object = (object) array(
            'user_id' => $follower->id,
            'online' => $user->updated_at

        );

        $notication->NotificationHelper($user->id, $body, $body, 'SellerProfile', json_encode($object), $follower->id, null);

        if ($follow) {
            return response()->json(['follow' => true]);
        }

        return response()->json(['error' => "Error encountered during the request"]);
    }



    //Get number of followers and following

    public function get_profile_count(Request $request)
    {

        $following_status = false;
        $following_count = Follow::where('user_id', $request->user_id)->count();
        $followers_count = Follow::where('follower_id', $request->user_id)->count();
        $product_count = product::where('user_id', $request->user_id)->count();
        $favorites = WishList::where('user_id', $request->user_id)->count();
        $userData = User::find($request->user_id);

        if ($request->follower_id) {
            $find_status = Follow::where('user_id', $request->follower_id)
                ->where('follower_id', $request->user_id)->first();
            if ($find_status) {
                $following_status = true;
            }
        }

        return response()->json([
            'following' => $following_count,
            'product' => $product_count,
            'followers' => $followers_count,
            'favorites' => $favorites,
            'user' => $userData,
            'follower_status' => $following_status
        ]);
    }

    public function get_follower_following()
    {

        $user = auth()->user();

        $following = Follow::join('users', 'follows.follower_id', '=', 'users.id')
            ->where('follower_id', $user->id)->get();

        $followers = Follow::join('users', 'follows.user_id', '=', 'users.id')
            ->where('user_id', $user->id)->get();


        return response()->json([
            'followers' => $followers,
            'status' => true,
            'following' => $following
        ]);
    }



    //Get list of followers and following

    public function get_profile_list(Request $request)
    {

        $followers = Follow::join('users', 'follows.user_id', '=', 'users.id')
            ->where('follower_id', $request->user_id)->get();

        $following = Follow::join('users', 'follows.follower_id', '=', 'users.id')
            ->where('user_id', $request->user_id)->get();


        return response()->json(['followers' => $followers, 'following' => $following]);
    }

    public function get_user_data(Request $request)
    {

        $user = User::find($request->user_id);

        return response()->json(['user' => $user]);
    }


    //deleting user account

    function delete_user_account(Request $request)
    {


        /**Validate the data using validation rules
         */




        $user_type = $request->registration_type == 1 ? 'required' : '';


        $validator = Validator::make($request->all(), [

            'user_id' => 'required',

            //   'reason' => 'required',
            //'password' => $user_type,
            //'email' => 'required'
        ]);
        /**Check the validation fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }




        if ($request->registration_type == 1) {

            $loginData = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);
            //checking authentication match for user type User 


            if (!auth()->attempt($loginData)) {
                return response(['message' => 'Invalid Password', 'invalid' => true]);
            }
        }


        // delete user products

        $products = product::where('user_id', $request->user_id)->get();


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

        // find  user from the system
        $user = User::find($request->user_id);


        if ($user) {

            $store_reason = AcountDelete::Create([
                'name' => $user->full_name,
                'reason' => $request->reason,
                'email' => $user->email,
                'phone' => $user->phone

            ]);

            if ($store_reason) {

                $user->delete();

                return response()->json(['message' => true]);
            }
        }


        return response()->json(['message' => false]);
    }
}
