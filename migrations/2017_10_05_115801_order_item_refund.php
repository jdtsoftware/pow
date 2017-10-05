<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class OrderItemRefund extends \JDT\Pow\BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_item_refund', function(Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid');

            $table->unsignedInteger('order_id');
            $table->unsignedInteger('order_item_id');
            $table->decimal('total_amount', 19,4);
            $table->decimal('total_vat', 19,4);
            $table->mediumInteger('tokens_adjustment');
            $table->text('reason')->nullable();
            $table->string('payment_gateway_reference')->nullable();
            $table->text('payment_gateway_blob')->nullable(); //to split out to key->value table?
            $table->unsignedInteger('created_user_id');

            $this->timestampsAndSoftDeletes($table);

            $table->foreign('order_id')->references('id')->on('order');
            $table->foreign('order_item_id')->references('id')->on('order_item');
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
