<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('b_logo');
            $table->integer('seller_id');
            $table->string('b_name')->nullable();
            $table->tinyInteger('approved')->default(0);
            $table->string('b_phone')->nullable();
            $table->string('b_email')->nullable();
            $table->string('b_website')->nullable();
         //   $table->string('b_email')->nullable();
            $table->string('b_country')->nullable();
            $table->integer('number_of_employee')->nullable();
            $table->string('years_established')->nullable();
            $table->text('b_description')->nullable();
            $table->integer('stage')->default(1);
            $table->string('b_country_location')->nullable();
            $table->string('b_district')->nullable();
            $table->string('b_subcounty')->nullable();
            $table->string('b_physical_address')->nullable();
            $table->string('b_postall_address')->nullable();
            $table->string('manager_logo')->nullable();
            $table->string('manager_job_title')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_phone')->nullable();
            $table->string('manager_whatsap_phone')->nullable();
            $table->string('manager_country')->nullable();
            $table->string('manager_email')->nullable();
            $table->json('product_category')->nullable();
            $table->integer('main_category')->nullable();
            $table->string('reg_certificate')->nullable();
            $table->string('tax_certificate')->nullable();
            $table->string('trading_licence')->nullable();
            $table->string('reg_certificate_file')->nullable();
            $table->string('tax_certificate_file')->nullable();
            $table->string('trading_licence_file')->nullable();
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
        Schema::dropIfExists('sellers');
    }
}
