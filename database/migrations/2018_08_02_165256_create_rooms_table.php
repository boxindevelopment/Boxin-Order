<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('space_id')->unsigned();
            $table->integer('types_of_size_id')->unsigned();
            $table->string('name', 225);
            $table->string('id_name', 12)->nullable();
            $table->integer('status_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign('space_id')->references('id')->on('spaces')
                ->onUpdate('cascade')->onDelete('cascade');
            $table
                ->foreign('types_of_size_id')->references('id')->on('types_of_size')
                ->onUpdate('cascade')->onDelete('cascade');
            $table
                ->foreign('status_id')->references('id')->on('status')
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
        Schema::dropIfExists('rooms');
    }
}
