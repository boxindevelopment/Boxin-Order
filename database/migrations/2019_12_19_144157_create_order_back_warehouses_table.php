<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderBackWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_back_warehouses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('types_of_pickup_id')->unsigned();
            $table->integer('order_detail_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->date('date');
            $table->time('time');
            $table->string('address');
            $table->double('deliver_fee');
            $table->string('time_pickup', 50);
            $table->text('note');
            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign('types_of_pickup_id')->references('id')->on('types_of_pickup');
            $table
                ->foreign('order_detail_id')->references('id')->on('order_details');
            $table
                ->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_back_warehouses');
    }
}
