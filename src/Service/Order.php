<?php

namespace JDT\Pow\Service;

use \JDT\Pow\Interfaces\Basket as iBasket;
use \JDT\Pow\Interfaces\Wallet as iWallet;

/**
 * Class Pow.
 */
class Order
{
    protected $models;
    protected $order;

    public function __construct()
    {
        $this->models = \Config::get('pow.models');
    }

    public function findById(int $orderId)
    {
        return $this->models['order']::find($orderId);
    }

    public function createFromBasket(iWallet $wallet, iBasket $basket)
    {
        $basketItems = $basket->getBasket();
        if(empty($basketItems['products'])) {
            throw new \Exception('Basket is empty - you cannot create an order with an empty basket!');
        }

        $order = $this->models['order']::create([
            'wallet_id' => $wallet->getId(),
            'order_status_id' => 1,
            'total_price' => $basket->getTotalPrice(),
            'created_user_id' => 1,
        ]);

        foreach($basketItems['products'] as $productId => $item) {
            $order->addLineItem($item['product'], $item['qty'] ?? 1);
        }

        return $order;
    }


}
