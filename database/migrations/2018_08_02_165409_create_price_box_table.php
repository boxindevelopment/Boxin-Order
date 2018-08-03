<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceBoxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_box', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type_size_id')->unsigned();
            $table->integer('type_duration_id')->unsigned();
            $table->integer('price');
            $table->timestamps();

            $table
                ->foreign('type_size_id')->references('id')->on('types_of_size')
                ->onUpdate('cascade')->onDelete('cascade');
            $table
                ->foreign('type_duration_id')->references('id')->on('types_of_duration')
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
