<?php

namespace JDT\Pow\Service;

use \JDT\Pow\Interfaces\Basket as iBasket;
use \JDT\Pow\Interfaces\Wallet as iWallet;
use \JDT\Pow\Interfaces\Order as iOrder;
use JDT\Pow\Interfaces\Entities\Order as iOrderEntity;
use Ramsey\Uuid\Uuid;

/**
 * Class Pow.
 */
class Order implements iOrder
{
    protected $models;
    protected $order;

    public function __construct()
    {
        $this->models = \Config::get('pow.models');
    }

    /**
     * @param int $orderId
     * @return mixed
     */
    public function findById(int $orderId) : iOrderEntity
    {
        return $this->models['order']::find($orderId);
    }

    /**
     * @param $uuid
     * @return iOrderEntity
     */
    public function findByUuid($uuid) : iOrderEntity
    {
        return $this->models['order']::where('uuid', $uuid)->first();
    }

    /**
     * @param iWallet $wallet
     * @param iBasket $basket
     * @return iOrderEntity
     * @throws \Exception
     */
    public function createFromBasket(iWallet $wallet, iBasket $basket) : iOrderEntity
    {
        $basketItems = $basket->getBasket();
        if(empty($basketItems['products'])) {
            throw new \Exception('Basket is empty - you cannot create an order with an empty basket!');
        }

        $order = $this->models['order']::create([
            'uuid' => Uuid::uuid4()->toString(),
            'wallet_id' => $wallet->getId(),
            'order_status_id' => 1,
            'original_total_price' => $basket->getTotalPrice(),
            'adjusted_total_price' => $basket->getTotalPrice(),
            'created_user_id' => 1,
        ]);

        foreach($basketItems['products'] as $productId => $item) {
            $order->addLineItem($item['product'], $item['qty'] ?? 1);
        }

        return $order;
    }


}
