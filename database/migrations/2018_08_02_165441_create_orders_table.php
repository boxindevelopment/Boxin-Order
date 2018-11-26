<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('space_id')->unsigned();
            $table->integer('qty');
            $table->integer('total');
            $table->string('id_name',50)->nullable();
            $table->integer('status_id')->unsigned();
            $table->timestamps();

            $table
                ->foreign('user_id')->references('id')->on('users');
            $table
                ->foreign('space_id')->references('id')->on('spaces')
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
        Schema::dropIfExists('orders');
    }
}
