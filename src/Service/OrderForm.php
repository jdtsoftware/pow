<?php

namespace JDT\Pow\Service;


use \JDT\Pow\Interfaces\OrderForm as iOrderForm;


/**
 * Class OrderForm.
 */
class OrderForm implements iOrderForm
{
    protected $models;
    protected $order;

    public function __construct()
    {
        $this->models = \Config::get('pow.models');
    }

    public function getForm()
    {

    }

}
