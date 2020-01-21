<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('transaction_type', 30)->default('start storing');
            $table->integer('order_id');
            $table->string('status', 20);
            $table->string('location_warehouse', 50);
            $table->string('location_pickup', 100)->nullable();
            $table->datetime('datetime_pickup')->nullable();
            $table->integer('types_of_box_space_small_id');
            $table->integer('space_small_or_box_id');
            $table->double('amount');
            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign('types_of_box_space_small_id')->references('id')->on('types_of_box_room');
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
        Schema::dropIfExists('transaction_logs');
    }
}
