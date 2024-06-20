<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->string('full_name');
            $table->string('email')->unique()->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_type')->nullable();
            $table->string('user_status')->nullable();
            $table->string('picture')->nullable();
            $table->string('avatar_google')->nullable();
            $table->string('registration_type')->nullable();
            $table->string('about_user')->nullable();
            $table->string('user_intention')->nullable();
            $table->string('location')->nullable();
            $table->string('language')->nullable();
            $table->string('site_url')->nullable();
            $table->string('gender')->nullable();
            $table->tinyInteger('registered_from')->nullable();
            $table->tinyInteger('verification_status')->nullable();
            $table->string('image_cover')->nullable();
            $table->string('country')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('physical_address')->nullable();
            $table->string('country_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('fb_username')->nullable();
            $table->string('twitter_username')->nullable();
            $table->string('linkedin_username')->nullable();
            $table->string('youtube_username')->nullable();
            $table->tinyInteger('online_status')->nullable();
            $table->integer('auth_code')->nullable();
            $table->json('permission')->nullable();        
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
