<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Wallet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createLookupTable('wallet_token_type', true, true);

        Schema::table('wallet_token_type', function(Blueprint $table) {
            //so we can support tokens and monetary values
            $table->string('icon')->after('id');
        });

        $this->createLookupTable('wallet_transaction_type', true);
        $this->createLookupTable('order_status', true);
        $this->createLookupTable('payment_gateway', true);



        Schema::create('product', function(Blueprint $table) {
            $table->increments('id');
            $table->string('handle');
            $table->string('name');
            $table->text('description');
            $table->decimal('total_price', 19,4);
            $table->unsignedInteger('created_user_id')->nullable();

            $this->timestampsAndSoftDeletes($table);

            $table->foreign('created_user_id')->references('id')->on('user');
        });

        Schema::create('product_token', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('wallet_token_type_id');
            $table->unsignedMediumInteger('tokens');

            $this->timestampsAndSoftDeletes($table);

            $table->foreign('product_id')->references('id')->on('product');
            $table->foreign('wallet_token_type_id')->references('id')->on('wallet_token_type');
        });

        Schema::create('product_config', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->string('key');
            $table->string('value');

            $this->timestampsAndSoftDeletes($table);

            $table->foreign('product_id')->references('id')->on('product');

            $table->unique(['product_id','key']);
        });

        Schema::create('product_adjustment_price', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->string('criteria');
            $table->string('adjustment');

            $this->timestampsAndSoftDeletes($table);

            $table->foreign('product_id')->references('id')->on('product');
        });

        Schema::create('wallet', function(Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid');
            $table->unsignedSmallInteger('overdraft');

            $this->timestampsAndSoftDeletes($table);
            $table->unique('uuid');
        });

        Schema::create('order', function(Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid');
            $table->unsignedInteger('wallet_id');
            $table->unsignedInteger('order_status_id');
            $table->unsignedInteger('payment_gateway_id');

            $table->integer('vat_percentage')->default(0);
            $table->decimal('original_vat_price', 19,4);
            $table->decimal('original_total_price', 19,4);
            $table->decimal('adjusted_vat_price', 19,4);
            $table->decimal('adjusted_total_price', 19,4);

            $table->string('po_number')->nullable();
            $table->string('payment_gateway_reference')->nullable();
            $table->text('payment_gateway_blob')->nullable(); //to split out to key->value table?
            $table->unsignedInteger('created_user_id');

            $this->timestampsAndSoftDeletes($table);

            $table->foreign('wallet_id')->references('id')->on('wallet');
            $table->foreign('order_status_id')->references('id')->on('order_status');
            $table->foreign('payment_gateway_id')->references('id')->on('payment_gateway');

            $table->unique('uuid');
        });

        Schema::create('order_item', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('quantity');
            $table->mediumInteger('tokens_total');
            $table->mediumInteger('tokens_spent');

            $table->decimal('original_unit_price', 19,4);
            $table->decimal('adjusted_unit_price', 19,4);

            $table->decimal('original_total_price', 19,4);
            $table->decimal('adjusted_total_price', 19,4);

            $table->integer('vat_percentage')->default(0);
            $table->decimal('original_vat_price', 19,4);
            $table->decimal('adjusted_vat_price', 19,4);

            $this->timestampsAndSoftDeletes($table);

            $table->foreign('order_id')->references('id')->on('order');
            $table->foreign('product_id')->references('id')->on('product');
        });

        Schema::create('wallet_token', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('wallet_id');
            $table->unsignedInteger('wallet_token_type_id');
            $table->mediumInteger('tokens');

            $this->timestampsAndSoftDeletes($table);

            $table->foreign('wallet_id')->references('id')->on('wallet');
            $table->foreign('wallet_token_type_id')->references('id')->on('wallet_token_type');
        });

        Schema::create('wallet_transaction', function(Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid');
            $table->unsignedInteger('wallet_id');
            $table->unsignedInteger('wallet_token_id');
            $table->unsignedInteger('wallet_transaction_type_id');
            $table->mediumInteger('tokens');
            $table->unsignedInteger('order_item_id');
            // == https://laravel.com/docs/5.4/eloquent-relationships#polymorphic-relations ==
            $table->unsignedInteger('transaction_linker_id');
            $table->string('transaction_linker_type');
            // == https://laravel.com/docs/5.4/eloquent-relationships#polymorphic-relations ==
            $table->unsignedInteger('created_user_id');

            $table->unique('uuid');
            $this->timestampsAndSoftDeletes($table);

            $table->foreign('wallet_id')->references('id')->on('wallet');
            $table->foreign('wallet_token_id')->references('id')->on('wallet_token');
            $table->foreign('wallet_transaction_type_id')->references('id')->on('wallet_transaction_type');
            $table->foreign('order_item_id')->references('id')->on('order_item');
            $table->foreign('created_user_id')->references('id')->on('user');
        });

        DB::table('order_status')->insert([
            ['handle' => 'draft',   'name' => 'Awaiting Payment'],
            ['handle' => 'pending', 'name' => 'Awaiting Payment'],
            ['handle' => 'paid',    'name' => 'Paid'],
            ['handle' => 'complete','name' => 'Complete'],
            ['handle' => 'refund',  'name' => 'Refunded'],
            ['handle' => 'cancelled',  'name' => 'Cancelled'],
        ]);

        DB::table('payment_gateway')->insert([
            ['handle' => 'stripe', 'name' => 'Stripe']
        ]);

        DB::table('wallet_transaction_type')->insert([
            ['handle' => 'credit', 'name' => 'Credit'],
            ['handle' => 'debit', 'name' => 'Debit']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /**
            DROP TABLE IF EXISTS wallet_transaction;
            DROP TABLE IF EXISTS wallet_token;
            DROP TABLE IF EXISTS wallet_organisation_linker;
            DROP TABLE IF EXISTS order_item;
            DROP TABLE IF EXISTS product_adjustment_price;
            DROP TABLE IF EXISTS product_token;
            DROP TABLE IF EXISTS product_config;
            DROP TABLE IF EXISTS product;
            DROP TABLE IF EXISTS `order`;
            DROP TABLE IF EXISTS wallet;
            DROP TABLE IF EXISTS wallet_token_type;
            DROP TABLE IF EXISTS wallet_transaction_type;
            DROP TABLE IF EXISTS order_status;
            DROP TABLE IF EXISTS payment_gateway;
         */
    }

    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param bool                                  $index
     *
     * @return \Illuminate\Database\Schema\Blueprint
     */
    protected function timestamps(Blueprint $table, bool $index = true):Blueprint
    {
        $table->dateTime('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
        $table->dateTime('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

        if ($index === true) {
            $table->index('created_at');
            $table->index('updated_at');
        }

        return $table;
    }

    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     *
     * @return \Illuminate\Database\Schema\Blueprint
     */
    protected function softDeletes(Blueprint $table):Blueprint
    {
        $table->dateTime('deleted_at')->nullable();
        $table->index('deleted_at');

        return $table;
    }

    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     *
     * @return \Illuminate\Database\Schema\Blueprint
     */
    protected function timestampsAndSoftDeletes(Blueprint $table):Blueprint
    {
        $this->timestamps($table);
        $this->softDeletes($table);

        return $table;
    }

    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param bool                                  $unique
     *
     * @return \Illuminate\Database\Schema\Blueprint
     */
    protected function handle(Blueprint $table, bool $unique = false):Blueprint
    {
        $table->string('handle');
        if ($unique === true) {
            $table->unique('handle');
        } else {
            $table->index('handle');
        }

        return $table;
    }

    /**
     * @param string $tableName
     * @param bool   $withName
     * @param bool   $withDesc
     *
     * @return \Illuminate\Database\Schema\Blueprint
     */
    protected function createLookupTable(string $tableName, $withName = false, $withDesc = false)
    {
        return \Schema::create($tableName, function (Blueprint $table) use ($withName, $withDesc) {
            $table->increments('id');
            $this->handle($table, true);
            if ($withName === true) {
                $table->string('name');
            }
            if ($withDesc == true) {
                $table->text('description');
            }

            $this->timestampsAndSoftDeletes($table);
        });
    }
}
