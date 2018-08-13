<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTypesOfSizeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('types_of_size', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('types_of_box_room_id')->unsigned();
            $table->string('name', 50);
            $table->integer('code');
            $table->timestamps();

            $table
                ->foreign('types_of_box_room_id')->references('id')->on('types_of_box_room')
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
        Schema::dropIfExists('types_of_size');
    }
}
