<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class OrderProductShop extends \JDT\Pow\BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_item', function(Blueprint $table) {
            $table->unsignedInteger('product_shop_id')->after('product_id')->nullable();
            $table->foreign('product_shop_id')->references('id')->on('product_shop');
        });

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
