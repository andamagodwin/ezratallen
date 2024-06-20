<?php

namespace App\Http\Controllers\API\Auth;

//use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\seller\RegisterSellerRequest;
use App\Mail\EmailNotification;
use App\OTPAuth;
use App\Sellers;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SellerAuthController extends Controller
{
    //Register seller 

    public function registerSeller(RegisterSellerRequest $request)
    {

        if ($request->stage == 1) {
            return $request->saveFirstStage();
        }

        if ($request->stage == 2) {
            return $request->saveSecondStage();
        }


        if ($request->stage == 3) {
            return $request->saveThirdStage();
        }


        if ($request->stage == 4) {
            ///mundruku's code ..
            return $request->saveLastStage();
        }
    }

    public function loginSeller(Request $request)
    {


        $loginData = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);



        $data = $request->all();

        $userExists = User::where('email', $data['email'])->first();

        if (!$userExists) {
            return response(['message' => 'User email not found', "result" => false], 404);
        }

        if ($userExists && $userExists->account_types != 'farmer') {
            return response(['message' => 'No assciated records found in our system'], 403);
        }

        //checking authentication match for user type User 


        if (!auth()->attempt($loginData)) {
            return response(['message' => 'Invalid Password'], 401);
        }


        //Checking my rules

        //Generating access  token for the User
        $accessToken = auth()->user()->createToken('authToken')->accessToken;


        $userData = auth()->user();
        $userData->token = $accessToken;

        //get farmsers record

        $farmer = Sellers::where('seller_id', $userData->id)->first();
        //Returning response for  the user 
        // Convert both objects to arrays
        // Convert Eloquent objects to arrays
        $userDataArray = $userData->toArray();
        $farmerArray = $farmer->toArray();

        // Merge the arrays
        $combinedData = array_merge($userDataArray, $farmerArray);

        // Convert back to an object if you want
        $combinedObject = (object) $combinedData;

        return response(['data' => $combinedObject, 'result' => true]);
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

    public function resendOtpToken(Request $request)
    {

        try {
            $code = rand(123189, 787998);
            $body = 'Your Farmsell Verification code is ' . $code;

            //event(new SmsNotificationEvent($request->phone, $body));
            $email_object = new EmailNotification('', $body);

            Mail::to($request->email)->send($email_object);

            OTPAuth::create([
                'otp_code' => $code
            ]);

            return response()->json(['result' => true]);
        } catch (Exception $e) {
            return response()->json(['result' => $e->getMessage()], 500);
        }
    }
}
