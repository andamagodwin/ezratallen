<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('product_id');
            $table->integer('qty');
            $table->string('unit');
            $table->integer('customer_id');
            $table->integer('seller_id');
            $table->text('instruction')->nullable();
            $table->float('amount', 32, 2);
            $table->string('phone');
            $table->string('address');
            $table->string('name');
            $table->string('product_name')->nullable();
            $table->string('order_number')->nullable();
            $table->enum('order_status', [0, 1, 2, 3, 4, 5, 6])->default(0);
            $table->enum('declined_by', [0, 1])->default(0);
            $table->enum('view_status', [0, 1])->default(0);
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
        Schema::dropIfExists('product_orders');
    }
}
