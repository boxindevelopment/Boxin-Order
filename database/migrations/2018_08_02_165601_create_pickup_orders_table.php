<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePickupOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pickup_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->unsigned();
            $table->integer('types_of_pickup_id')->unsigned();
            $table->text('address')->nullable();
            $table->string('latitude', 225)->nullable();
            $table->string('longitude', 225)->nullable();
            $table->date('date');
            $table->time('time');
            $table->text('note')->nullable();
            $table->integer('pickup_fee');
            $table->string('driver_name', 50)->nullable();
            $table->string('driver_phone', 50)->nullable();
            $table->integer('status_id')->default(10)->unsigned();
            $table->timestamps();

            $table
                ->foreign('order_id')->references('id')->on('orders')
                ->onUpdate('cascade')->onDelete('cascade');
            $table
                ->foreign('types_of_pickup_id')->references('id')->on('types_of_pickup')
                ->onUpdate('cascade')->onDelete('cascade');
            $table
                ->foreign('status_id')->references('id')->on('status')
                ->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('pickup_orders');
    }
}
