<?php

namespace App\Http\Controllers\API\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\User;

class ForgotPasswordController extends Controller
{
    //Sending password reset link to the email


   public function forgot_password(Request $request)
   {

       $input = $request->all();
       $rules = array(
           'email' => "required|email",
       );

       $validator = Validator::make($input, $rules);
       if ($validator->fails()) {
           $arr = array("status" => 400, "message" => $validator->errors()->first(), "data" => array());
       }

        else {

         $user=User::where('email', $request->email)->first();
         if($user->registration_type==2)
         
         {
             return response()->json(['error'=>'You have registered using your google account,  please use google social authentication your to login']);
         }

         if($user->registration_type==2){
            return response()->json(['error'=>'You have registered using your facebook account,  please use your facebook social authentication account to login']);
          }


           try {

               $response = Password::sendResetLink($request->only('email'), function (Message $message) {

                   $message->subject($this->getEmailSubject());

               });

               switch ($response) {
                   case Password::RESET_LINK_SENT:

                       return \Response::json(array("status" => 200, "message" => trans($response), "data" => array()));
                   case Password::INVALID_USER:

                       return \Response::json(array("status" => 400, "message" => trans($response), "data" => array()));
               }
           } 

           catch (\Swift_TransportException $ex) {
               $arr = array("status" => 400, "message" => $ex->getMessage(), "data" => []);
           } 

           catch (Exception $ex) {
               $arr = array("status" => 400, "message" => $ex->getMessage(), "data" => []);
           }
       }


       return \Response::json($arr);
   }


     //Resetting the paasword using the token from the email address



   public function reset_password(Request $request)
   {
       //Validate input
       $validator = Validator::make($request->all(), [
           'email' => 'required|email|exists:users,email',
           'password' => 'required|confirmed',
           'token' => 'required' ]);

       //check if payload is valid before moving on

       if ($validator->fails()) {
           return response()->json([ 'error'=> $validator->errors() ]);
       }


       // Validate the token
      $tokenData = DB::table('password_resets')
       ->where('email', $request->email)->first();

       if (!$tokenData){
        return response()->json(['error'=>'Password recent session expired']);
       }
       
       //get the email owner of the token result

       $user = User::where('email', $tokenData->email)->first();

       // Redirect the user back if the email is invalid
           if (!$user) 
           {
            return response()->json(['error'=>'User not found']);
           }


      //Hash and update the new password

          $password = $request->password;
          $user->password = \Hash::make($password);
          $user->update(); //or $user->save();

    //Delete the token
       DB::table('password_resets')->where('email', $user->email)
             ->delete();



       return response()->json(['success'=>'Succesfully changed your password']);



   }






}
