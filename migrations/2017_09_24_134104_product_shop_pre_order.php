<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ProductShopPreOrder extends \JDT\Pow\BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_shop', function(Blueprint $table) {
            //so we can support tokens and monetary values
            $table->boolean('order_approval_required')->after('product_id')->default(0);
            $table->boolean('quantity_lock')->after('quantity')->default(0);
        });

        Schema::create('product_shop_order_form', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');

            $table->boolean('hidden');
            $table->string('validation');
            $table->string('type');
            $table->string('name');

            $this->timestampsAndSoftDeletes($table);

            $table->foreign('product_id')->references('id')->on('product');
        });

        Schema::create('order_form', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('product_shop_order_form_id');
            $table->text('value');

            $this->timestampsAndSoftDeletes($table);

            $table->foreign('order_id')->references('id')->on('order');
            $table->foreign('product_shop_order_form_id')->references('id')->on('product_shop_order_form');
        });

        DB::table('order_status')->insert([
            ['handle' => 'pending_approval', 'name' => 'Awaiting Approval'],
        ]);
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
