<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductChatHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_chat_histories', function (Blueprint $table) {


            $table->bigIncrements('id');
            $table->integer('sender_id');
            $table->integer('receiver_id');
            $table->integer('product_id');
            $table->text('message');
            $table->integer('last_send_id');
            $table->smallInteger('read_status')->default(0);
            
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
        Schema::dropIfExists('product_chat_histories');
    }
}
