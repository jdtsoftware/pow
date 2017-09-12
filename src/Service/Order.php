<?php

namespace JDT\Pow\Service;

use \JDT\Pow\Interfaces\Basket as iBasket;

/**
 * Class Pow.
 */
class Order
{
    protected $models;
    protected $order;

    public function __construct()
    {
        $this->models = Config::get('pow.models');
    }

    public function findById(int $orderId)
    {
        return $this->models['order']::find($orderId);
    }

    public function create(\JDT\Pow\Interfaces\Wallet $wallet, iBasket $basket)
    {
        $models = Config::get('pow.models');
        $basketItems = $basket->getBasket();

        $order = $models['order']::create([
            'wallet_id' => $wallet->getId(),
            'order_status_id' => 1,
            'total_price' => $basket->getTotalPrice(),
            'created_user_id' => 1,
        ]);

        return $this;
    }


}
