<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewNotificationColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            // O is when the icon is not clicked, 1 is when the notification is viewed 

            /*
            $table->enum('status', [0, 1, 2, 3])->default(0);
            $table->string('screen')->nullable();
            $table->text('mobile_data')->nullable();
            $table->json('screen_object')->nullable();
            $table->string('web_url')->nullable();
            */
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            // O is when the icon is not clicked, 1 is when the notification is viewed 
            /*
            $table->enum('status', [0, 1, 2, 3])->default(0);
            $table->string('screen')->nullable();
            $table->text('mobile_data')->nullable();
            $table->json('screen_object')->nullable();
            $table->string('web_url')->nullable();
            */
        });
    }
}
