<?php

namespace App\Http\Controllers\API;

use App\ContactUs;
use App\Events\ContactUsEvent;
use App\HelpCount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SupportController extends Controller
{
    //contact us function
    public function Contact_us(Request $request)
    {


        /**Validate the data using validation rules
         */

         

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required'
        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $data = $request->all();

        $contact = ContactUs::create($data);

        event(new ContactUsEvent($contact->subject, $contact->message, $contact->email, $contact->name));


        return response()->json(['message' => true]);
    }



    //Support help No/Yes Data base persisting

    public function was_this_helpful(Request $request)
    {


        $user = HelpCount::where('user_id', $request->user_id)->first();

        $data = $request->all();

        if ($user) {

            if ($user->status != $request->status) {
                $user->delete();
                HelpCount::create($data);
            }
            return;
        }
        HelpCount::create($data);
    }

    //Support help records query


    public function get_was_this_helpful(Request $request)
    {
        $exist_status = false;

        if ($request->user_id) {
            $user = HelpCount::where('user_id', $request->user_id)->first();


            if ($user) {
                $exist_status = $user->status;
            }
        }

        $help = HelpCount::latest()->first();

        return response()->json([
            'help' => $help,
            'status' => $exist_status
        ]);
    }
}
