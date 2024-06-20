<?php

namespace App\Http\Requests\Agent;

use App\FarmsellAgent;
use App\Mail\FarmsellAgent as MailFarmsellAgent;
use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class saveAgent extends FormRequest
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
        return [
            //
        ];
    }


    public function save()
    {


        //check if the form submission is in edit mode 
        if ($this->id) {

            $application = FarmsellAgent::find($this->id);

            if ($application) {

                //save the changes base on form stage submitted 

                if ($this->stage == 1) {

                    $application->personal_details = $this->personal_details;
                    $application->form_stage = $this->stage;
                    $application->save();


                    return response()->json(['result' => $application]);
                }


                if ($this->stage == 2) {

                    $application->contact_details = $this->contact_details;
                    $application->form_stage = $this->stage;
                    $application->save();



                    return response()->json(['result' => $application]);
                }


                if ($this->stage == 3) {

                    $application->social_media_numbers = $this->social_media_numbers;
                    $application->form_stage = $this->stage;
                    $application->save();


                    return response()->json(['result' => $application]);
                }

                if ($this->stage == 4) {

                    $application->education = $this->education;
                    $application->form_stage = $this->stage;
                    $application->save();


                    return response()->json(['result' => $application]);
                }

                if ($this->stage == 5) {

                    $application->experience = $this->experience;
                    $application->form_stage = $this->stage;
                    $application->save();


                    return response()->json(['result' => $application]);
                }

                if ($this->stage == 6) {

                    $application->soft_profesional_skills = $this->soft_profesional_skills;
                    $application->form_stage = $this->stage;
                    $application->save();


                    return response()->json(['result' => $application]);
                }

                if ($this->stage == 7) {

                    $application->motivation = $this->motivation;
                    $application->form_stage = $this->stage;
                    $application->save();


                    return response()->json(['result' => $application]);
                }

                if ($this->stage == 8) {

                    $application->knowing_farmsell = $this->knowing_farmsell;
                    $application->form_stage = $this->stage;
                    $application->save();

                    return response()->json(['result' => $application]);
                }

                if ($this->stage == 9) {

                    $application->form_stage = $this->stage;
                    $application->save();

                    //send an email on completing the from application







                    if ($application->personal_details && $application->contact_details) {



                        $data = $application->personal_details;
                        $data_sec = $application->contact_details;

                        $data->email = str_replace(' ', '', strtolower($data_sec->email));
                        $data->phone_real = $data_sec->phone_real;
                        $data->country_code = $data_sec->country_code;
                        $data->subject = 'Your Application with Farmsell';
                        $youtube = env('youtube');
                        $linkedin = env('linkedin');
                        $fb = env('fb');
                        $twitter = env('twitter');
                        $instagram = env('instagram');


                        $body = <<<EOD
                        <p>Thanks for the successful submission to Farmsell. We do confirm receipt of your application for <span style='font-weight:bold'>Farmsell Agent</span> with Farmsell digital marketplace.  We are very delighted about your interest in joining Farmsell</p>
                        <p> Your application is currently under review. We shall be contacting you if you meet the criteria for the role. Please make sure the phone and email you shared with us are valid and accessible.</p>
                           
                        <p>You can also subscribe to our <a  href='$youtube'> YouTube </a> channel or newsletters to receive the latest updates. Please follow us on <a  href='$twitter'> Twitter </a>, <a  href='$fb'>Facebook</a>, <a  href='$instagram'>Instagram</a>, or<a  href='$linkedin'> LinkedIn</a> to see the latest interesting developments at Farmsell including promotions or prizes. Don’t Miss the opportunity.</p>
                        EOD;

                        $data->body = $body;


                        $email_object = new MailFarmsellAgent($data);
                        //check the existence of the users number / email
                        $email = User::where('email', $data->email)->first();

                        $phone = User::where('phone', $data->email)->first();

                        $login = 'https://farmsell.org/SignIn';
                        $contact = 'https://farmsell.org/contactus';
                        Mail::to(strtolower($data->email))->send($email_object);

                        if (!$email && !$phone) {

                            $password = $this->generateRandomString(6);

                            User::create([
                                'full_name' => $data->name . ' ' . $data->other_names,
                                'email' => strtolower($data->email),
                                'password' => Hash::make($password),
                                'user_type' => 'user',
                                'registration_type' => 4,
                                'country_code' => $data->country_code,
                                'phone' => $data->phone_real,


                            ]);
                            $body = <<<EOD
                            <p> Congratulations 😇 😇 😇! </p>
                            <p>A new account has been created for you at Farmsell with the following details. </p>
                            <div style='margin-left:20px'>
                            <p><span style='font-weight:bold'>1) Email:</span> $data->email </p>
                            <p><span style='font-weight:bold'>2) Password:</span> $password </p>
                            </div>
                            <p>You can click <a  href='$login'>  here </a>to sign into your account where you will be required to create a new password for your account at Farmsell.</p>
                            <p> Please note that this is a single-use temporary password that expires in 24 hours.  In case you can’t remember your password, please click “forgot password” on the sign-in page to get a new temporary password. </p>
                               
                            <p>You can also subscribe to our <a  href='$youtube'> YouTube </a> channel or newsletters to receive the latest updates. Please follow us on <a  href='$twitter'> Twitter </a>, <a  href='$fb'>Facebook</a>, <a  href='$instagram'>Instagram</a>, or<a  href='$linkedin'> LinkedIn</a> to see the latest interesting developments at Farmsell including promotions or prizes. Don’t Miss the opportunity.</p>

                            <p> Should you have any further queries or need more assistance, please <a  href='$contact'>contact us. </a> </p>
                            EOD;

                            $data->body = $body;
                            $data->subject = "Your new account with Farmsell";

                            $email_object = new MailFarmsellAgent($data);

                            Mail::to(strtolower($data->email))->send($email_object);
                        }
                    }












                    return response()->json(['result' => $application]);
                }
            }
        } else {


            $application = FarmsellAgent::create([

                'knowing_farmsell' => $this->knowing_farmsell,
                'motivation' => $this->motivation,
                'soft_profesional_skills' => $this->soft_profesional_skills,
                'experience' => $this->experience,
                'education' => $this->education,
                'social_media_numbers' => $this->social_media_numbers,
                'contact_details' => $this->contact_details,
                'personal_details' => $this->personal_details,
                'form_stage' => 1
            ]);

            //send an email to the user 






            return response()->json(['result' => $application]);
        }
    }


    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
