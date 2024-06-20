<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommunityQuestionRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_question_replies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('community_question_id');
            $table->bigInteger('user_id');
            $table->string('text');
            $table->string('image');
            $table->string('emoji');
            $table->boolean('file');
            $table->integer('likes_count');
            $table->integer('replies_count');
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
        Schema::dropIfExists('community_question_replies');
    }
}
