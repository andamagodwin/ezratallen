<?php

namespace App\Http\Controllers\API\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Redirect;

class VerificationApiController extends Controller
{
    //



   use VerifiesEmails;
   /**
   * Show the email verification notice.
   *
   */
   public function show()
   {
   //
   }
  /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function verify(Request $request)
    {

      
        auth()->loginUsingId($request->route('id'));
       
        if ($request->route('id') != $request->user()->getKey()) {
            throw new AuthorizationException;
        }

        if ($request->user()->hasVerifiedEmail()) {
            return response()->json('User already have verified email!', 422);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return Redirect::to('https://farmsell.org/signIn');
    }

   /**
   * Resend the email verification notification.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
   public function resend($request, $id)
   {

    auth()->loginUsingId($id);

   if ($request->user()->hasVerifiedEmail()) {

     return false;
   // return redirect($this->redirectPath());
   }
   
   $request->user()->sendApiEmailVerificationNotification();
   return response()->json('Verification email has been sent to your mail');
   // return back()->with(‘resent’, true);
   }
}
