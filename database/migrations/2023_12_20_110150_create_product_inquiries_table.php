<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductInquiriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        //Schema::disableForeignKeyConstraints();

        Schema::create('product_inquiries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('description');
            $table->unsignedBigInteger('user_id');
            $table->integer('quantity');
            $table->string('contact');
            $table->unsignedBigInteger('product_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
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
        
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('product_inquiries');
    }
}
