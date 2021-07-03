<?php
/*
 * File name: 2021_01_25_212252_create_bookings_table.php
 * Last modified: 2021.04.20 at 11:19:32
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBookingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('e_provider');
            $table->longText('e_service');
            $table->longText('options')->nullable();
            $table->smallInteger('quantity')->nullable()->default(1);
            $table->bigInteger('user_id')->nullable()->unsigned();
            $table->integer('booking_status_id')->nullable()->unsigned();
            $table->longText('address');
            $table->integer('payment_id')->nullable()->unsigned();
            $table->longText('coupon')->nullable();
            $table->longText('taxes')->nullable();
            $table->dateTime('booking_at')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->text('hint')->nullable();
            $table->boolean('cancel')->nullable()->default(0);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('set null');
            $table->foreign('booking_status_id')->references('id')->on('booking_statuses')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bookings');
    }
}
