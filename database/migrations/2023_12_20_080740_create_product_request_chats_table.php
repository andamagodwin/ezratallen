<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductRequestChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_request_chats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('message');
            $table->timestamp('seen_at')->nullable();
            $table->timestamp('message_deleted_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Use unsignedBigInteger instead of foreignId
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->unsignedBigInteger('product_request_id');
            //s $table->unsignedBigInteger('reply_id')->nullable();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_request_id')->references('id')->on('product_requests')->onDelete('cascade');
            //$table->foreign('reply_id')->references('id')->on('chats')->onDelete('cascade');

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
        Schema::dropIfExists('product_request_chats');
    }
}
