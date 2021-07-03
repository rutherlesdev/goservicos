<?php
/*
 * File name: 2021_01_13_111155_create_e_providers_table.php
 * Last modified: 2021.04.20 at 11:19:32
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEProvidersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('e_providers', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('name')->nullable();
            $table->integer('e_provider_type_id')->unsigned();
            $table->longText('description')->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('mobile_number', 50)->nullable();
            $table->double('availability_range', 9, 2)->nullable()->default(0);
            $table->boolean('available')->nullable()->default(1);
            $table->boolean('featured')->nullable()->default(0);
            $table->boolean('accepted')->nullable()->default(0);
            $table->timestamps();
            $table->foreign('e_provider_type_id')->references('id')->on('e_provider_types')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('e_providers');
    }
}
