<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Users extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         * категории кол-во?
         * 
         */
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('location')->nullable();
            $table->string('lon')->nullable();
            $table->string('lat')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('type');
            $table->string('social_hash')->nullable();
            $table->string('imei');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->integer('balance')->default(0);
            
            $table->string('token');
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
        Schema::dropIfExists('users');
    }
}
