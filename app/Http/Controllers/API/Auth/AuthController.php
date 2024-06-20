<?php

namespace App\Http\Controllers\API\Auth;

use App\Events\SmsNotificationEvent;
use App\Http\Controllers\Controller;
use App\User;
use App\Events\UserRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\Auth\VerificationApiController;
use App\Mail\AdminNotification;
use App\Mail\EmailNotification;
use App\Mail\PasswordReset;
use App\Mail\UserWelcome;
use App\OTPAuth;
use App\product;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Image;
use Illuminate\Support\Str;

class AuthController extends Controller
{


    //registering user for web

    public function register(Request $request)
    {

        //Social media registration

        if ($request->registration_type == 2 || $request->registration_type == 3 || $request->registration_type == 4) {
            $phone_validate = '';
        } else {
            $phone_validate = 'required';
        }

        /**Validate the data using validation rules
         */


        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'user_type' => 'required',
            'phone' => $phone_validate,
            'registration_type' => 'required'
        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        /**Store all values of the fields
         */
        $newuser = $request->all();

        /**Create an encrypted password using the hash
         */
        $newuser['password'] = Hash::make($newuser['password']);

        /**Insert a new user in the table
         */



        $user = User::create($newuser);

        /**Create an access token for the user
         */
        $success['token'] = $user->createToken('AgroMarket')->accessToken;
        //email verification



        //send welcome email to the adminstrator
        /*
        $message = "A new user" . $request->full_name . " has registered on Farmsell via the website";

        event(new UserRegister($message));
        */

        $email_object = new UserWelcome($request->full_name);
        $admin_object = new AdminNotification($user->full_name, $user->email, $user->phone);


        try {


            if ($request->registration_type == 1) {

                $email_send = new VerificationApiController();

                $email_send->resend($request, $user->id);
            }

            Mail::to($user->email)->later(now()->addMinutes(1), $email_object);


            Mail::to('sell@farmsell.org')
                ->bcc(['ivanmundruku@gmail.com'])
                ->later(now()->addMinutes(1), $admin_object);

            /*
            //send to ivan

            Mail::to('ivanmundruku@gmail.com')->later(now()->addMinutes(1), $admin_object);


            //send to all the registered admin 
            $admins = User::where('user_type', 'admin')->get();

            foreach ($admins as $admin) {

                Mail::to($admin->email)->later(now()->addMinutes(1), $admin_object);
            }
            */
        } catch (Exception $e) {
        }






        //send an email verification to the user 


        //$user->sendApiEmailVerificationNotification();


        /**Return success message with token value
         */
        if ($request->registration_type == 2 || $request->registration_type == 3 || $request->registration_type == 4) {

            $user = User::find($user->id);
            $user->email_verified_at = Carbon::now();
            $user->save();
        }

        return response()->json(['success' => $success,  'user' => $user], 200);
    }




    //registering user for web

    public function registerHelper($request)
    {

        //Social media registration

        if ($request->registration_type == 2 || $request->registration_type == 3 || $request->registration_type == 4) {
            $phone_validate = '';
        } else {
            $phone_validate = 'required';
        }

        /**Validate the data using validation rules
         */


        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'user_type' => 'required',
            'phone' => $phone_validate,
            'registration_type' => 'required',
            'apple_token' => 'required'
        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        /**Store all values of the fields
         */
        $newuser = $request->all();

        /**Create an encrypted password using the hash
         */
        $newuser['password'] = Hash::make($newuser['password']);

        /**Insert a new user in the table
         */



        $user = User::create($newuser);

        /**Create an access token for the user
         */
        $accessToken = $user->createToken('AgroMarket')->accessToken;
        //email verification



        //send welcome email to the adminstrator
        /*
         $message = "A new user" . $request->full_name . " has registered on Farmsell via the website";
 
         event(new UserRegister($message));
         */

        $email_object = new UserWelcome($request->full_name);
        $admin_object = new AdminNotification($user->full_name, $user->email, $user->phone);


        try {


            if ($request->registration_type == 1) {

                $email_send = new VerificationApiController();

                $email_send->resend($request, $user->id);
            }

            Mail::to($user->email)->later(now()->addMinutes(1), $email_object);


            Mail::to('sell@farmsell.org')
                ->bcc(['ivanmundruku@gmail.com'])
                ->later(now()->addMinutes(1), $admin_object);

            /*
             //send to ivan
 
             Mail::to('ivanmundruku@gmail.com')->later(now()->addMinutes(1), $admin_object);
 
 
             //send to all the registered admin 
             $admins = User::where('user_type', 'admin')->get();
 
             foreach ($admins as $admin) {
 
                 Mail::to($admin->email)->later(now()->addMinutes(1), $admin_object);
             }
             */
        } catch (Exception $e) {
        }






        //send an email verification to the user 


        //$user->sendApiEmailVerificationNotification();


        /**Return success message with token value
         */
        if ($request->registration_type == 2 || $request->registration_type == 3 || $request->registration_type == 4) {

            $user = User::find($user->id);
            $user->email_verified_at = Carbon::now();
            $user->save();
        }

        return response(['user' => $user, 'user_type' => 'user', 'access_token' => $accessToken]);
    }








    //registering user for web

    public function registerMobile(Request $request)
    {

        /**Validate the data using validation rules
         */


        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'password' => 'required|min:6',
            'user_type' => 'required',
            'phone' => 'required',
            'country_code' => 'required',
            'registration_type' => 'required',

        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }
        //Chec for the existence of an email
        if ($request->email) {
            $check_user = User::where('email', $request->email)->first();
            if ($check_user) {
                return response()->json(['exists' => 'Email already exists']);
            }
        }

        /**Store all values of the fields
         */
        $newuser = $request->all();

        /**Create an encrypted password using the hash
         */
        $newuser['password'] = Hash::make($newuser['password']);

        /**Insert a new user in the table
         */
        $user = User::create($newuser);
        $path = null;

        /**Create an access token for the user
         */
        $success['token'] = $user->createToken('AgroMarket')->accessToken;

        //send welcome email to the user and adminstrator

        //event(new UserRegister($request->email, $request->full_name));

        //send an email verification to the user 

        if ($request->hasFile('picture')) {



            $fileName = md5(microtime()) . '_avatars.' . $request->picture->getClientOriginalExtension();

            $image_path =  public_path("storage/avatars/" . $fileName);

            Image::make($request->picture)->resize(320, 240)->save($image_path);


            $path = 'avatars/' . $fileName;
        }


        //$user->sendApiEmailVerificationNotification();

        /**Return success message with token value
         */
        $user = User::find($user->id);

        //$user->email_verified_at = Carbon::now();
        $user->picture = $path;
        $user->save();

        // $message = "A new user" . $request->full_name . " has registered on Farmsell via the mobile app";

        // event(new UserRegister($message));


        $email_object = new UserWelcome($request->full_name);
        $admin_object = new AdminNotification($user->full_name, $user->email, $user->phone);


        try {


            Mail::to($user->email)->later(now()->addMinutes(1), $email_object);


            //send to ivan


            Mail::to('sell@farmsell.org')
                // ->bcc(['ivanmundruku@gmail.com'])
                ->later(now()->addMinutes(1), $admin_object);

            //send to all the registered admin 
            /*
           $admins = User::where('user_type', 'admin')->get();
           
           foreach ($admins as $admin) {

               Mail::to($admin->email)->later(now()->addMinutes(1), $admin_object);
               
           }
           */
        } catch (Exception $e) {
        }


        return response()->json(['success' => $success,  'user' => $user], 200);
    }


      //registering user for web

      public function registerMobileV2(Request $request)
      {
  
          /**Validate the data using validation rules
           */
  
  
          $validator = Validator::make($request->all(), [
              'full_name' => 'required',
              'password' => 'required|min:6',
              //'user_type' => 'required',
              //'phone' => 'required',
              //'country_code' => 'required',
              'registration_type' => 'required',
  
          ]);
  
          /**Check the validation becomes fails or not*/
          if ($validator->fails()) {
              /**Return error message
               */
              return response()->json(['error' => $validator->errors()]);
          }
          //Chec for the existence of an email
          if ($request->email) {
              $check_user = User::where('email', $request->email)->first();
              if ($check_user) {
                  return response()->json(['exists' => 'Email already exists']);
              }
          }
  
          /**Store all values of the fields
           */
          $newuser = $request->all();
  
          /**Create an encrypted password using the hash
           */
          $newuser['password'] = Hash::make($newuser['password']);
  
          /**Insert a new user in the table
           */
          $user = User::create($newuser);
          $path = null;
  
          /**Create an access token for the user
           */
          $success['token'] = $user->createToken('AgroMarket')->accessToken;
  
          /**Return success message with token value
           */
          $user = User::find($user->id);
  
          //$user->email_verified_at = Carbon::now();
          $user->picture = $path;
          $user->save();

  
  
          $email_object = new UserWelcome($request->full_name);
          //$admin_object = new AdminNotification($user->full_name, $user->email, $user->phone);
  
  
          try {
  
  
              Mail::to($user->email)->later(now()->addMinutes(1), $email_object);
  

          } catch (Exception $e) {
          }
  
  
          return response()->json(['success' => $success,  'user' => $user], 200);
      }



    public function sendOtpCodeAdmin(Request $request)
    {

        $check = User::where('email', $request->email)->first();

        if (!$check || $check->user_type != 'admin') {
            return response()->json(['error' => 'User does not exist']);
        }

        $code = rand(123189, 787998);
        $body = 'Your Farmsell Amin Password reset code is ' . $code;

        //event(new SmsNotificationEvent($request->phone, $body));

        $email_object = new EmailNotification('', $body);

        Mail::to($request->email)->send($email_object);

        OTPAuth::create([
            'otp_code' => $code
        ]);

        return response()->json(['result' => true]);
    }

    public function sendOtpCode(Request $request)
    {
        $code = rand(123189, 787998);
        $body = 'Your Farmsell Verification code is ' . $code;

        event(new SmsNotificationEvent($request->phone, $body));

        OTPAuth::create([
            'otp_code' => $code
        ]);

        return response()->json(['result' => true]);
    }

    public function validateOtpcode(Request $request)
    {
        $check_code = OTPAuth::where('otp_code', $request->code)->first();

        if ($check_code) {

            $check_code->delete();
            return response()->json(['result' => true]);
        }

        return response()->json(['result' => false]);
    }



    //recover password changes
    public function admin_recover_password(Request $request)
    {

        $user = User::where('email', $request->email)->first();

        if ($user) {


            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json(['result' => true]);
        }
    }
    //recover password changes
    public function change_password_recover(Request $request)
    {

        $user = User::where('email', $request->contact)->first();

        if (!$user) {
            $user::where('phone', $request->contact)->first();
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['result' => true]);
    }



    //User login function 

    public function login(Request $request)
    {




        //check if request for apple






        if ($request->registration_type == 4 && $request->email) {


            //check if user exists

            $exist = User::where('email', $request->email)->first();

            if ($exist && $exist->registration_type == 4) {
                $request['email'] = $exist ? $exist->email : '';
                $request['password'] = 'googlesignin';
            }

            if ($exist) {
                return response()->json(['exist' => true]);
            }


            return $this->registerHelper($request);
        }



        if ($request->registration_type == 4 && !$request->email) {

            $appleUser = User::where('apple_token', $request->apple_token)->first();
            $request['email'] = $appleUser ? $appleUser->email : '';
            $request['password'] = 'googlesignin';
        }


        $loginData = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $data = $request->all();
        $userCount = User::where('email', $data['email']);
        $role_check = User::where('email', $data['email'])->first();

        if (!$userCount->count()) {
            return response(['error' => 'User email not found']);
        }

        //checking user role in the User Auth controller 



        //checking authentication match for user type User 


        if (!auth()->attempt($loginData)) {
            return response(['message' => 'Invalid Password']);
        }



        if (!$role_check->email_verified_at) {

            $email_send = new VerificationApiController();

            $status = $email_send->resend($request, $role_check->id);
            return response(['verify' => "email sent to verify your account"]);
        }

        //checking email verification status

        // if($role_check->email_verified_at==NULL){
        //     return response(['error' => 'Please verify your']);
        // } 

        //check user type from the database 

        if (auth()->user()->user_type == 'admin') {
            return response(['user_type_error' => 'Your are unauthorized to access account']);
        };


        //Generating access  token for the User
        $accessToken = auth()->user()->createToken('authToken')->accessToken;


        //Returning response for  the user 

        return response(['user' => auth()->user(), 'user_type' => 'user', 'access_token' => $accessToken]);
    }



    //Login with mobile using both email and phone

    public function loginMobile(Request $request)
    {

        $check_login = $request->phone ? 'phone' : 'email';

        $loginData = $request->validate([
            $check_login => 'required',
            'password' => 'required',

        ]);

        $data = $request->all();
        $userCount = User::where('email', $data['email'])
            ->orWhere('phone', $data['email'])->first();
        //$role_check = User::where('email', $data['email'])->first();

        if (!$userCount) {
            return response(['error' => 'User email not found']);
        }



        if ($userCount && $userCount->account_types == 'farmer') {
            return response(['error' => 'No assciated records found in our system']);
        }

        //

        //checking user role in the User Auth controller 



        //checking authentication match for user type User 


        if (!auth()->attempt($loginData)) {
            return response(['message' => 'Invalid Password']);
        }

        //checking email verification status

        // if($role_check->email_verified_at==NULL){
        //     return response(['error' => 'Please verify your']);
        // } 

        //check user type from the database 

        if (auth()->user()->user_type == 'admin') {
            return response(['user_type_error' => 'Your are unauthorized to access account']);
        };


        //Generating access  token for the User
        $accessToken = auth()->user()->createToken('authToken')->accessToken;


        //Returning response for  the user 

        return response(['user' => auth()->user(), 'user_type' => 'user', 'access_token' => $accessToken]);
    }






    //password update 

    public function user_password_update(Request $request)
    {

        //get requests

        $request_data = $request->All();

        // validate the new password


        $validator = Validator::make($request->all(), [
            'password_old' => 'required|min:6',
            'password' => 'required'
        ]);


        /**Check the validation becomes fails or not
         */
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        if (!Hash::check($request->password_old, $request->user()->password)) {

            return response(['error' => 'Wrong password']);
        }

        if ($request->password_old == $request->password) {

            return response(['error2' => 'New password can not be the same as old']);
        }

        $user_id = Auth::User()->id;

        $obj_user = User::find($user_id);
        $obj_user->password = Hash::make($request_data['password']);
        $obj_user->save();
        return response(['success' => 'you have Successfully changed your password']);
    }


    //User Logout funtion

    public function logout(Request $request)
    {


        $accessToken = auth()->user()->token();


        $token_check = $request->user()->tokens->find($accessToken);


        $token_check->revoke();
        $token_check->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }


    //Check email verification

    public function verifyCheck(Request $request, $id)
    {


        $email_send = new VerificationApiController();

        $status = $email_send->resend($request, $id);
        if (!$status) {
            $user = User::find($id);

            return response()->json(['message' => 'Email Already verified', 'user' => $user, 'status' => true]);
        }

        return response()->json(['message' => 'We have emailed, please check your email to verify it', 'status' => false]);
    }


    //Email verification check

    public function verificationConfirm($id)
    {

        $user = User::where('email', $id)->first();

        if ($user && $user->email_verified_at) {
            return response()->json(['message' => true]);
        }

        return response()->json(['message' => false]);
    }

    //Check weather number is taken

    public  function  checkNumber(Request $request)
    {

        $check = User::where('phone', $request->phone)->first();

        if ($check) {
            return response()->json(['message' => true]);
        }

        return response()->json(['message' => false]);
    }


    public function PasswordRecovery(Request $request)
    {

        $user = null;

        if ($this->checkEmail($request->email)) {
            $user = User::where('email', $request->email)->first();
        } else {
            $user = User::where('phone', $request->email)->first();
        }

        $code = rand(123112, 787997);


        if (!$user) {
            return response()->json(['result' => false, 'message' => 'User not found']);
        }

        if ($user->registration_type == 2 || $user->registration_type == 3) {
            return response()->json(['result' => false, 'message' => 'You can not recover your password 
            since you registered with google/facebook account
            ']);
        }




        if ($user->phone) {
            $body = 'Your Farmsell Verification code is ' . $code;
            $phone_minus_zero = substr($user->phone, 1);
            $full_phone = $user->country_code . $phone_minus_zero;

            event(new SmsNotificationEvent($full_phone, $body));

            $user->password = Hash::make($code);
            $user->save();
        }


        //genenerate a random 8 digit password


        $email_object = new PasswordReset(
            $code,
            'Your Farmsell Verification code is:',
            'Farmsell Password Recover Request',
            ' ',
            ' '
        );



        //send email notification to the user 
        if ($user->email) {
            Mail::to($user->email)->send($email_object);
        }

        //event(new SmsNotificationEvent($request->phone, $body));

        OTPAuth::create([
            'otp_code' => $code,
            //'phone' => $user->phone,
            //'email' => $user->email
        ]);



        return response()->json([
            'result' => true,
            'message' =>
            'We have emailed/Sms you a verification'
        ]);
    }

    public function ValidToken()
    {
        return response()->json(['result' => true]);
    }

    public function checkEmail($email)
    {
        $find1 = strpos($email, '@');
        $find2 = strpos($email, '.');
        return ($find1 !== false && $find2 !== false && $find2 > $find1);
    }

    //testing 

    public function details()
    {

        $product = product::all();


        foreach ($product as $c) {
            $user = User::find($c->user_id);
            if ($c->latitue) {

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
