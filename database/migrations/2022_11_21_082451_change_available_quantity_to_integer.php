<?php

use App\product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAvailableQuantityToInteger extends Migration
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
            $product = product::all();

            //convert string values to intener
            foreach ($product as $d) {
                if (!is_numeric($d->available_quantity)) {
                    $d->available_quantity = 0;
                    $d->save();
                }
            }
            $table->bigInteger('available_quantity')->charset(null)->collation(null)->change();
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
            //
            $table->bigInteger('available_quantity')->charset(null)->collation(null)->change();
        });
    }
}
