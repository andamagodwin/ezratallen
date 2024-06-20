<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeleteStatusToProductChatHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_chat_histories', function (Blueprint $table) {
            //
            $table->json('delete_permission')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_chat_histories', function (Blueprint $table) {
            //
            $table->json('delete_permission')->nullable();
        });
    }
}
