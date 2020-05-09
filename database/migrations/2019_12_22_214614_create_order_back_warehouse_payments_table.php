<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderBackWarehousePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_back_warehouse_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_detail_id')->unsigned();
            $table->integer('order_back_warehouse_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('payment_type', 50)->nullable();
            $table->string('bank', 50)->nullable();
            $table->integer('amount');
            $table->string('id_name', 50)->nullable();
            $table->text('midtrans_url')->nullable();
            $table->datetime('midtrans_start')->nullable();
            $table->datetime('midtrans_expired')->nullable();
            $table->text('midtrans_response')->nullable();
            $table->string('midtrans_status', 30)->nullable();
            $table->integer('status_id')->unsigned();
            $table->timestamps();

            $table
                ->foreign('order_detail_id')->references('id')->on('order_details')
                ->onUpdate('cascade')->onDelete('cascade');
            $table
                ->foreign('order_back_warehouse_id')->references('id')->on('order_back_warehouses')
                ->onUpdate('cascade')->onDelete('cascade');
            $table
                ->foreign('user_id')->references('id')->on('users')
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
        Schema::dropIfExists('order_back_warehouse_payments');
    }
}
