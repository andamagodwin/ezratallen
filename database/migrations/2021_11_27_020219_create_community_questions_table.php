<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommunityQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('community_id');
            $table->bigInteger('user_id');
            $table->string('topic');
            $table->string('details');
            $table->string('type');
            $table->boolean('notify_reply');
            $table->integer('views_count');
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
        Schema::dropIfExists('community_questions');
    }
}
