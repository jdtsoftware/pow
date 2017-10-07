<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class WalletTokenTypeConfig extends \JDT\Pow\BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_token_type_config', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('wallet_token_type_id');
            $table->string('key');
            $table->string('value');

            $this->timestampsAndSoftDeletes($table);

            $table->foreign('wallet_token_type_id')->references('id')->on('wallet_token_type');

            $table->unique(['wallet_token_type_id','key']);
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
