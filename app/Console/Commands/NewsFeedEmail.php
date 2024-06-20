<?php

namespace App\Console\Commands;

use App\EmailLogs;
use App\Mail\AdminNotificationUpdate;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NewsFeedEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:letter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'News letter email update to  all the users';

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
        $news = EmailLogs::where('status', 0)->first();

        if ($news) {

            $users = User::where('user_type', 'admin')->get();
            $index = 0;


            $message = $news->body;


            foreach ($users as $c) {

                $email_object = new AdminNotificationUpdate($c->full_name, $message, $news->title);

                try {

                    Mail::to($c->email)->send($email_object);

                    if ($index == 0) {
                        $news->message = $email_object->render();
                        $news->status = 1;
                        $news->save();
                    }
                } catch (\Exception $ex) {
                }
            }
        }
    }
}
