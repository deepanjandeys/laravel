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
            $table->date('order_date');
            $table->Integer('customer_id');
            $table->string('Customer_name');
            $table->string('address');
            $table->float('sale_price');
            $table->float('down_payment');
            $table->float('EMI');
            $table->tinyInteger('EMI_mode');
            $table->tinyInteger('EMI_Period');
            $table->tinyInteger('status');
            $table->timestamps();
            $table->timestamp('deleted_at');
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
