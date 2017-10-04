<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class OrderFormRename extends \JDT\Pow\BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_item_form', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_item_id');
            $table->unsignedInteger('product_shop_order_form_id');
            $table->text('value');

            $this->timestampsAndSoftDeletes($table);

            $table->foreign('order_item_id')->references('id')->on('order_item');
            $table->foreign('product_shop_order_form_id')->references('id')->on('product_shop_order_form');
        });

        Schema::drop('order_form');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
