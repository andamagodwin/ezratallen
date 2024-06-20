<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFeatureColumnToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            //
            $table->boolean('is_inquiry')->default(0);
            $table->string('brand_name')->nullable();
            $table->integer('minimum_order_qty')->nullable();
            $table->bigInteger('min_price')->nullable();
            $table->integer('low_bid_qty')->nullable();
            $table->integer('max_bid_qty')->nullable();
            $table->bigInteger('max_price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_inquiry')->default(0);
            $table->string('brand_name')->nullable();
            $table->integer('minimum_order_qty')->nullable();
            $table->bigInteger('min_price')->nullable();
            $table->integer('low_bid_qty')->nullable();
            $table->integer('max_bid_qty')->nullable();
            $table->bigInteger('max_price')->nullable();
        });
    }
}
