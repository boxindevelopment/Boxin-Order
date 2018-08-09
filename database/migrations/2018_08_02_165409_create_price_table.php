<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('types_of_box_room_id')->unsigned();
            $table->integer('types_of_size_id')->unsigned();
            $table->integer('types_of_duration_id')->unsigned();
            $table->integer('price');
            $table->timestamps();

            $table
                ->foreign('types_of_box_room_id')->references('id')->on('types_of_box_room')
                ->onUpdate('cascade')->onDelete('cascade');
            $table
                ->foreign('types_of_size_id')->references('id')->on('types_of_size')
                ->onUpdate('cascade')->onDelete('cascade');
            $table
                ->foreign('types_of_duration_id')->references('id')->on('types_of_duration')
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
        Schema::dropIfExists('price_box');
    }
}
