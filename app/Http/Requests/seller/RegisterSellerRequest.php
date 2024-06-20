<?php

namespace App\Http\Requests\seller;

use App\Mail\EmailNotification;
use App\OTPAuth;
use App\Sellers;
use App\User;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Image;

class RegisterSellerRequest extends FormRequest
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
        if ($this->stage == 1) {
            return [
                'email' => 'required|email|unique:users,email',
                'password' => 'required'
            ];
        }


        if ($this->stage == 2) {
            return [

                'b_logo' => 'required',
                'b_name' => 'required',
                'b_phone' => 'required',
                'b_description' => 'required',
                'b_country_location' => 'required',
                'b_district' => 'required',
                'b_subcounty' => 'required',
                'b_email' => 'required'

            ];
        }
        if ($this->stage == 3) {
            return [
                'manager_name' => 'required',
                'seller_id' => 'required'
            ];
        }
        if ($this->stage == 4) {
            return [
                'product_category' => 'required',
                'main_category' => 'required',
                'seller_id' => 'required'
            ];
        }
        if ($this->stage == 5) {
            return [
                //
            ];
        }

        return [
            "stage" => 'required'
        ];
    }

    public function saveFirstStage()
    {



        /**create user 
         */

        try {
            $user = User::create([
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'account_types' => 'farmer'
            ]);

            //send six digit integer 

            if (!$this->isGoogle) {
                $this->emailCode($user->email);
            }

            return response()->json([
                'result' => true,
                'data' => $user
            ]);
        } catch (Exception $e) {
            return response()->json(['result' => false], 500);
        }
    }

    public function saveSecondStage()
    {



        $user = User::where('email', $this->email)->first();

        if ($user) {

            $seller =  Sellers::create([
                'b_logo' => $this->storeProfile($this->b_logo),
                'seller_id' => $user->id,
                'b_name' => $this->b_name,
                'b_phone' => $this->b_phone,
                'b_email' => $this->b_email,
                'b_website' => $this->b_website,
                'b_country_location' => $this->b_country,
                'number_of_employee' => $this->number_of_employee,
                'years_established' => $this->years_established,
                'b_description' => $this->b_description,
                'b_district' => $this->b_district,
                'b_subcounty' => $this->b_subcounty,
                'b_physical_address' => $this->b_physical_address,
                'b_postall_address' => $this->b_postall_address,
                'stage' => 2

            ]);

            return response()->json([
                'result' => true,
                'data' => $seller
            ]);
        }

        return response()->json([
            'result' => false,
            'data' => []
        ], 404);
    }
    public function saveThirdStage()
    {

        $seller = Sellers::where('seller_id', $this->seller_id)->first();

        if ($seller) {

            $seller->manager_logo = $this->storeProfile($this->manager_logo);
            $seller->manager_job_title = $this->manager_job_title;
            $seller->manager_name = $this->manager_name;
            $seller->manager_phone = $this->manager_phone;
            $seller->manager_whatsap_phone = $this->manager_whatsap_phone;
            $seller->manager_country = $this->manager_country;
            $seller->manager_email = $this->manager_email;
            $seller->stage = 3;

            $seller->save();
            return response()->json([
                'result' => true,
                'data' => $seller
            ]);
        }


        return response()->json([
            'result' => false,
            'data' => []
        ], 404);
    }
    public function saveLastStage()
    {

        $seller = Sellers::where('seller_id', $this->seller_id)->first();

        $user = User::find($this->seller_id);

        if ($seller) {

            $seller->product_category = $this->product_category;
            $seller->main_category = $this->main_category;
            $seller->reg_certificate = $this->reg_certificate;
            $seller->trading_licence = $this->trading_licence;
            $seller->tax_certificate = $this->tax_certificate;
            $seller->stage = 4;

            $seller->save();

            //generate token

            $seller->token =  $user->createToken('AgroMarket')->accessToken;

            $userDataArray = $user->toArray();
            $farmerArray = $seller->toArray();

            // Merge the arrays
            $combinedData = array_merge($userDataArray, $farmerArray);

            // Convert back to an object if you want
            $combinedObject = (object) $combinedData;

            return response()->json([
                'result' => true,
                'data' => $combinedObject
            ]);
        }


        return response()->json([
            'result' => false,
            'data' => []
        ], 404);
    }

    public function storeProfile($b_logo)
    {

        $fileName = md5(microtime()) . '_cover.' . $b_logo->getClientOriginalExtension();

        $image_path =  public_path("storage/sellerAvator/" . $fileName);
        Image::make($b_logo)->save($image_path);

        $path = 'sellerAvator/' . $fileName;
        return $path;
    }

    public function storeDocuments($b_logo)
    {

        $fileName = md5(microtime()) . '_cover.' . $b_logo->getClientOriginalExtension();

        $image_path =  public_path("storage/sellerAvator/" . $fileName);
        Image::make($b_logo)->save($image_path);

        $path = 'sellerAvator/' . $fileName;
        return $path;
    }
    public function emailCode($email)
    {


        $code = rand(123189, 787998);
        $body = 'Your Farmsell Verification code is ' . $code;

        //event(new SmsNotificationEvent($request->phone, $body));
        $email_object = new EmailNotification('', $body);

        Mail::to($email)->send($email_object);

        OTPAuth::create([
            'otp_code' => $code
        ]);
    }
}
