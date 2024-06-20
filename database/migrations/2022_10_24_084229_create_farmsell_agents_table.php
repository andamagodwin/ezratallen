<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFarmsellAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('farmsell_agents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('personal_details')->nullable();
            $table->json('contact_details')->nullable();
            $table->json('social_media_numbers')->nullable();
            $table->json('education')->nullable();
            $table->json('experience')->nullable();

            $table->json('soft_profesional_skills')->nullable();
            $table->json('motivation')->nullable();
            $table->json('knowing_farmsell')->nullable();
            $table->tinyInteger('form_stage');
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
        Schema::dropIfExists('farmsell_agents');
    }
}
