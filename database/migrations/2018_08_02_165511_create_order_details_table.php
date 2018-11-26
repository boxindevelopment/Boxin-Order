<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->unsigned();
            $table->integer('types_of_box_room_id')->unsigned();
            $table->integer('types_of_duration_id')->unsigned();
            $table->integer('room_or_box_id');
            $table->integer('types_of_size_id')->unsigned();
            $table->string('name', 225);
            $table->integer('duration');
            $table->integer('amount');
            $table->date('start_date');
            $table->date('end_date');          
            $table->string('id_name',50)->nullable();
            $table->integer('status_id')->unsigned();
            $table->tinyInteger('is_returned')->default(0);
            $table->timestamps();

            $table
                ->foreign('order_id')->references('id')->on('orders')
                ->onUpdate('cascade')->onDelete('cascade');
            $table
                ->foreign('types_of_box_room_id')->references('id')->on('types_of_box_room')
                ->onUpdate('cascade')->onDelete('cascade');
            $table
                ->foreign('types_of_duration_id')->references('id')->on('types_of_duration')
                ->onUpdate('cascade')->onDelete('cascade');
            $table
                ->foreign('types_of_size_id')->references('id')->on('types_of_size');
            $table
                ->foreign('status_id')->references('id')->on('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_details');
    }
}
