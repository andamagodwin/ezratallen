<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->text('body');
            $table->integer('read_status')->default(0);
            $table->enum('status', [0, 1, 2, 3])->default(0);
            $table->enum('notification_type', [0,1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20])->default(0);
            $table->string('screen')->nullable();
            $table->text('mobile_data')->nullable();
            $table->tinyInteger('count_status')->nullable();
            $table->json('screen_object')->nullable();
            $table->string('web_url')->nullable();
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
        Schema::dropIfExists('notifications');
    }
}
