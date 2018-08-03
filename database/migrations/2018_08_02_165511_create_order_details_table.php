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
            $table->integer('type_duration_id')->unsigned();
            $table->integer('room_or_box_id');
            $table->string('type', 10);
            $table->integer('type_size_id')->unsigned();
            $table->string('name', 225);
            $table->integer('duration');
            $table->integer('amount');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table
                ->foreign('order_id')->references('id')->on('orders')
                ->onUpdate('cascade')->onDelete('cascade');
            $table
                ->foreign('type_duration_id')->references('id')->on('types_of_duration')
                ->onUpdate('cascade')->onDelete('cascade');
            $table
                ->foreign('type_size_id')->references('id')->on('types_of_size')
                ->onUpdate('cascade')->onDelete('cascade');
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
