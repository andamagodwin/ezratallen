<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountTypeColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->enum('account_types', ['farmer', 'admin', 'buyer', 'subadmin'])->default('buyer');
            $table->string('full_name')->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->enum('account_types', ['farmer', 'admin', 'buyer', 'subadmin'])->default('buyer');
            $table->string('full_name')->change()->nullable();
        });
    }
}
