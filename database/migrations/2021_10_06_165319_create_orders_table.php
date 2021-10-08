<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id();
            $table->string('order_number', 100);
            $table->string('customer_name');
            $table->string('license_plate');
            $table->bigInteger('car_type_id')->unsigned()->nullable();
            $table->foreign('car_type_id')->references('id')->on('car_types')->onDelete('cascade');
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('car_type_name')->nullable();
            $table->double('car_type_price')->nullable();
            $table->string('product_name')->nullable();
            $table->double('product_price')->nullable();
            $table->string('other_product_name')->nullable();
            $table->double('other_product_price')->nullable();
            $table->double('grand_total');
            $table->bigInteger('payment_method_id')->unsigned();
            $table->bigInteger('shop_id')->unsigned();
            $table->timestamps();
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
