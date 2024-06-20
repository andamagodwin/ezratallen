<?php

namespace App\Console\Commands;

use App\FarmsellAgent;
use App\Mail\FarmsellAgent as MailFarmsellAgent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class UpdateAgent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agent:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update agents who did not finish there application process';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //

        $youtube = env('youtube');
        $linkedin = env('linkedin');
        $fb = env('fb');
        $twitter = env('twitter');
        $instagram = env('instagram');
        $login = 'https://farmsell.org/SignIn';
        $contact = 'https://farmsell.org/contactus';


        $agents = FarmsellAgent::where('form_stage', '!=', 9);

        foreach ($agents as $c) {
            $data = json_decode($c->personal_details);
            $data_sec = json_decode($c->contact_details);

            $data->email = $data_sec->email;


            $body = <<<EOD
            <p>Greetings from Farmsell.  </p>
            <p>We’ve learned with excitement that you started filling out an application form to become a Farmsell Agent. </p>
      
            <p>You can click <a  href='$login'>  here </a>to sign into your account where you will be required to create a new password for your account at Farmsell.</p>
            <p> Please note that this is a single-use temporary password that expires in 24 hours.  In case you can’t remember your password, please click “forgot password” on the sign-in page to get a new temporary password. </p>
               
            <p>You can also subscribe to our <a  href='$youtube'> YouTube </a> channel or newsletters to receive the latest updates. Please follow us on <a  href='$twitter'> Twitter </a>, <a  href='$fb'>Facebook</a>, <a  href='$instagram'>Instagram</a>, or<a  href='$linkedin'> LinkedIn</a> to see the latest interesting developments at Farmsell including promotions or prizes. Don’t Miss the opportunity.</p>

            <p> Should you have any further queries or need more assistance, please <a  href='$contact'>contact us. </a> </p>
            EOD;

            $data->body = $body;
            $data->subject = "Your new account with Farmsell";

            $email_object = new MailFarmsellAgent($data);
            Mail::to($data->email)->send($email_object);
        }
    }
}
