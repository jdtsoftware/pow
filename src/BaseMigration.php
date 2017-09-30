<?php

namespace JDT\Pow;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class BaseMigration extends \Illuminate\Database\Migrations\Migration
{


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