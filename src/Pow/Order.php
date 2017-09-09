<?php

namespace JDT\Pow;

/**
 * Class Pow.
 */
class Order
{
    protected $models;
    protected $order;

    public function __construct(int $orderId)
    {
        $this->models = Config::get('pow.models');

        $this->order = $this->models['order']::find($orderId);
    }

    public function items()
    {

    }

    public function status()
    {
        return $this->order->status;
    }
}
