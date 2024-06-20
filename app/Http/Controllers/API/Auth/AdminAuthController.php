<?php

namespace App\Http\Controllers\API\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\PasswordReset;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AdminAuthController extends Controller
{


    public function add_administrator(Request $request)
    {



        /**Validate the data using validation rules
         */
        $email_required = $request->id ? '' : 'required|email|unique:users';

        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'email' => $email_required,
            'user_type' => 'required',
            //'registration_type' => 'required',
            'permission' => 'required',

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

        $password = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 6);

        $newuser['password'] = Hash::make($password);

        $newuser['permission'] = json_encode($request->permission);

        $newuser['email_verified_at'] =  Carbon::now();


        /**Insert a new user in the table
         */

        if ($request->id) {

            $user = User::find($request->id);

            if ($user) {

                $user->full_name = $request->full_name;

                $user->email = $request->email;
                //$user->password = $password;

                $user->permission = json_encode($request->permission);
                $user->save();


                //genenerate a random 8 digit password
                $body = 'Admin details editted and you have been added as ' . $request->details . ' on Farmsell platform. 
                 
                   ';

                $email_object = new PasswordReset(
                    '',
                    $body,
                    'Account Editted',
                    $user->full_name,
                    $user->email
                );

                //send email notification to the user 
                if ($user->email) {
                    Mail::to($user->email)->send($email_object);
                    return response()->json(['result' => true], 200);
                }
            }
        }



        $user = User::create($newuser);

        /**Create an access token for the user
         */
        $success['token'] = $user->createToken('AgroMarket')->accessToken;
        //email verification




        //send welcome email to the adminstrator

        //genenerate a random 8 digit password
        $body = 'You have been added as ' . $request->details . ' on Farmsell platform. 
        You  can use the email and password provided below to login
        ';

        $email_object = new PasswordReset(
            $password,
            $body,
            'Admin Account Created',
            $user->full_name,
            $user->email
        );

        //send email notification to the user 
        if ($user->email) {
            Mail::to($user->email)->send($email_object);
        }



        return response()->json(['result' => true], 200);
    }


    //Admin login function 

    public function admin_login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $data = $request->all();
        $userCount = User::where('email', $data['email']);

        if (!$userCount->count()) {
            return response(['error' => 'User email not found']);
        }

        if (!auth()->attempt($loginData)) {
            return response(['message' => 'Invalid Password']);
        }

        if (auth()->user()->user_type == 'user') {
            return response(['user_type_error' => 'Your are unauthorized to access account']);
        };


        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response(['user' => auth()->user(), 'access_token' => $accessToken]);
    }



    //Admin password update 

    public function admin_password_update(Request $request)
    {
        //get requests

        $request_data = $request->All();

        // validate the new password


        $validator = Validator::make($request->all(), [
            'password_old' => 'required|min:6',
            'password' => 'required|confirmed|min:6'
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

            return response(['error' => 'New password can not be the same as old']);
        }

        $user_id = Auth::User()->id;

        $obj_user = User::find($user_id);
        $obj_user->password = Hash::make($request_data['password']);
        $obj_user->save();
        return response(['success' => 'you have Successfully changed your password']);
    }


    //Admin Logout funtion

    public function admin_logout(Request $request)
    {
        $accessToken = auth()->user()->token();


        $token_check = $request->user()->tokens->find($accessToken);


        $token_check->revoke();
        $token_check->delete();

        return response()->json([
            'result' => true
        ]);
    }
}
