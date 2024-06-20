<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('category_id');
            $table->bigInteger('user_id');
            $table->integer('sub_category_id');
            $table->integer('negotiation_status');
            $table->bigInteger('view_count')->default(0);
            $table->string('product_title');
            $table->string('description');
            $table->string('picture');
            $table->bigInteger('price');
            $table->boolean('is_approved')->default(1);
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->string('address');
            $table->string('available_quantity');
            $table->string('units');
            $table->string('unit_cost')->nullable();
            $table->string('currency')->nullable();
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
        Schema::dropIfExists('products');
    }
}
