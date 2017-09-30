<?php

namespace JDT\Pow\Service;

use \JDT\Pow\Interfaces\Basket as iBasket;

use JDT\Pow\Interfaces\Gateway;
use JDT\Pow\Interfaces\IdentifiableId;
use \JDT\Pow\Interfaces\Wallet as iWallet;
use \JDT\Pow\Interfaces\Order as iOrder;
use JDT\Pow\Interfaces\Entities\Order as iOrderEntity;
use JDT\Pow\Interfaces\Entities\OrderItem as iOrderItemEntity;
use Ramsey\Uuid\Uuid;
use Illuminate\Events\Dispatcher;

/**
 * Class Pow.
 */
class Order implements iOrder
{
    protected $models;
    protected $order;

    public function __construct(Gateway $paymentGateway, iWallet $wallet, Dispatcher $events)
    {
        $this->models = \Config::get('pow.models');
        $this->paymentGateway = $paymentGateway;
        $this->wallet = $wallet;
        $this->events = $events;
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
     * @return mixed
     */
    public function list()
    {
        return $this->models['order']::where('wallet_id', $this->wallet->getId())->get();
    }

    /**
     * @param int $page
     * @param int $perPage
     * @return Collection
     */
    public function listAll($page = 1, $perPage = 15)
    {
        return $this->models['order']::simplePaginate($perPage);
    }

    /**
     * @return iOrderItemEntity
     */
    public function findEarliestRedeemableOrderItem() : iOrderItemEntity
    {
        return $this->models['order_item']::findEarliestRedeemableOrderItem();
    }

    /**
     * @param $uuid
     * @return bool
     */
    public function validOrder($uuid) : bool
    {
        try {
            $this->findByUuid($uuid);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param iBasket $basket
     * @return iOrderEntity
     * @throws \Exception
     */
    public function createFromBasket(iBasket $basket, IdentifiableId $creator) : iOrderEntity
    {
        $basketItems = $basket->getBasket();
        if(empty($basketItems['products'])) {
            throw new \Exception('Basket is empty - you cannot create an order with an empty basket!');
        }

        $prices = $basket->getTotalPrices();
        $order = $this->models['order']::create([
            'uuid' => Uuid::uuid4()->toString(),
            'wallet_id' => $this->wallet->getId(),
            'order_status_id' => $this->models['order_status']::handleToId('draft'),
            'payment_gateway_id' => 1,
            'original_total_price' => $prices->originalTotalPrice,
            'adjusted_total_price' => $prices->adjustedTotalPrice,
            'vat_percentage' => \Config::get('pow.vat'),
            'original_vat_price' => $prices->originalVat,
            'adjusted_vat_price' => $prices->adjustedVat,
            'created_user_id' => $creator->getId(),
        ]);

        foreach($basketItems['products'] as $productId => $item) {
            $order->addLineItem($item['product'], $item['product_shop'], $item['qty'] ?? 1);
        }

        $basket->clearBasket();

        $this->events->fire('order.created', $order);

        return $order;
    }

    /**
     * @param iOrderEntity $order
     * @param array $paymentData
     * @return Gateway
     */
    public function pay(iOrderEntity $order, $paymentData = []) : Gateway
    {
        $response = $this->paymentGateway->pay($order->getAdjustedPrice(), $paymentData);

        $order->update([
            'payment_gateway_reference' => $response->getReference(),
            'payment_gateway_blob' => json_encode($response->getData()),
            'order_status_id' => $response->isSuccessful() ? $this->models['order_status']::handleToId('paid') : $this->models['order_status']::handleToId('pending')
        ]);

        $this->events->fire('order.payed', $order);

        return $response;
    }

    /**
     * @return Collection
     */
    public function unfinishedOrders()
    {
        return $this->models['order']::where('order_status_id', $this->models['order_status']::handleToId('draft'))->get();
    }

}
