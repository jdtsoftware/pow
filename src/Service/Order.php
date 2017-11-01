<?php

namespace JDT\Pow\Service;

use \JDT\Pow\Interfaces\Basket as iBasket;

use JDT\Pow\Interfaces\Gateway;
use JDT\Pow\Interfaces\IdentifiableId;
use \JDT\Pow\Interfaces\Wallet as iWallet;
use \JDT\Pow\Interfaces\Order as iOrder;
use JDT\Pow\Interfaces\Entities\Order as iOrderEntity;
use JDT\Pow\Interfaces\Entities\OrderItem as iOrderItemEntity;
use JDT\Pow\Interfaces\WalletOwner;
use JDT\Pow\Traits\VatCharge;
use Ramsey\Uuid\Uuid;
use Illuminate\Events\Dispatcher;

/**
 * Class Order.
 */
class Order implements iOrder
{
    use VatCharge;

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
     * @param bool $limitByWallet
     * @return iOrderEntity
     */
    public function findById(int $orderId, $limitByWallet = false) : iOrderEntity
    {
        $order = $this->models['order']::where('id', $orderId);

        if($limitByWallet) {
            $order = $order->where('wallet_id', $this->wallet->getId());
        }

        return $order->first();
    }

    /**
     * @param $uuid
     * @param bool $limitByWallet
     * @return iOrderEntity
     */
    public function findByUuid($uuid, $limitByWallet = false) : iOrderEntity
    {
        $order = $this->models['order']::where('uuid', $uuid);

        if($limitByWallet) {
            $order = $order->where('wallet_id', $this->wallet->getId());
        }

        return $order->first();
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return $this->models['order']::where('wallet_id', $this->wallet->getId())->get();
    }

    /**
     * @param int $perPage
     * @param string $status
     * @return Collection
     */
    public function listAll($perPage = 15, $status = null, $search = null)
    {
        $order = new $this->models['order'];

        if(isset($status)) {
            $order = $order->where(
                'order_status_id',
                $this->models['order_status']::handleToId($status)
            );
        }

        if(isset($search)) {
            $order = $order
                ->orWhere('uuid', 'like', '%' . $search . '%')
                ->orWhere('payment_gateway_reference', 'like', '%' . $search . '%')
                ->orWhere('id', 'like', '%' . $search . '%');
        }

        return $order->simplePaginate($perPage);
    }

    /**
     * @param $tokenTypeHandle
     * @return iOrderItemEntity
     */
    public function findEarliestRedeemableOrderItem($tokenTypeHandle) : iOrderItemEntity
    {
        return $this->models['order_item']::findEarliestRedeemableOrderItem($this->wallet->getId(), $tokenTypeHandle);
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
     * @param IdentifiableId $creator
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
        $address = $this->wallet->getOwner()->getAddress();

        $order = $this->models['order']::create([
            'uuid' => Uuid::uuid4()->toString(),
            'wallet_id' => $this->wallet->getId(),
            'order_status_id' => $this->models['order_status']::handleToId('draft'),
            'payment_gateway_id' => 1,
            'original_total_price' => $prices->originalTotalPrice,
            'adjusted_total_price' => $prices->adjustedTotalPrice,
            'original_vat_price' => $prices->originalVat,
            'adjusted_vat_price' => $prices->adjustedVat,
            'created_user_id' => $creator->getId(),
            'address_id' => $address->getId(),
            'address_type' => $address->getType()
        ]);

        foreach($basketItems['products'] as $productShopId => $item) {
            $productVat = isset($item['vat_percentage']) ? $item['vat_percentage'] : $this->wallet->getVatPerecentage();

            $orderItem = $order->addLineItem($item['product'], $item['product_shop'], $item['qty'] ?? 1, $productVat);

            if(!empty($basketItems['order_forms'][$productShopId]['data'])) {
                $orderFormData = $basketItems['order_forms'][$productShopId]['data'];
                foreach($orderFormData as $inputName => $value) {
                    $productshopOrderFormId = last(explode('_', $inputName));
                    $orderItem->addFormItem($productshopOrderFormId, $value);
                }
            }

            if(isset($item['product_shop']) && $item['product_shop']->order_approval_required) {
                $order->update([
                    'order_status_id' => $this->models['order_status']::handleToId('pending_approval')
                ]);
            }
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
        $paymentData['metadata'] = [
            'Invoice' => $order->getId(),
            'Order reference' => $order->getUuid(),
            'Organisation' => $this->wallet->getOwner()->companyName()
        ];

        $response = $this->paymentGateway->pay($order->getAdjustedPrice(), $paymentData);

        $order->update([
            'po_number' => $paymentData['po_number'] ?? null,
            'payment_gateway_reference' => $response->getReference(),
            'payment_gateway_blob' => json_encode($response->getData()),
            'order_status_id' => $response->isSuccessful() ? $this->models['order_status']::handleToId('paid') : $this->models['order_status']::handleToId('pending')
        ]);

        $this->events->fire('order.paid', $order);

        return $response;
    }

    /**
     * @return Collection
     */
    public function unfinishedOrders()
    {
        return $this->models['order']::where('order_status_id', $this->models['order_status']::handleToId('draft'))->get();
    }

    /**
     * @param $uuid
     * @return iOrderEntity|null
     */
    public function approveOrder($uuid)
    {
        $order = $this->findByUuid($uuid);
        if($order) {
            $order->update([
                'order_status_id' => $this->models['order_status']::handleToId('draft')
            ]);

            $this->events->fire('order.approved', $order);

            return $order;
        }

        return null;
    }

    /**
     * @param iOrderEntity $order
     * @param IdentifiableId $creator
     * @param $items
     * @param null $reason
     * @return bool
     */
    public function refund(iOrderEntity $order, IdentifiableId $creator, $items, $reason = null)
    {
        $totalAmount = 0;
        $refundedItems = [];
        foreach($items as $itemId => $quantity) {
            $item = $order->item($itemId);
            if(empty($item)) {
                continue;
            }

            $amount = $quantity * $item->adjusted_unit_price;
            $vat = $this->getVatCharge($amount, $item->vat_percentage);

            $originalUnitToken = $item->tokens_total / $item->quantity;
            $tokenAdjustment = $originalUnitToken * $quantity;
            $refundedItems[] = [
                'item' => $item,
                'total_amount' => $amount,
                'total_vat' => $vat,
                'token_adjustment' => $tokenAdjustment > $item->tokensAvailable() ? $item->tokensAvailable() : $tokenAdjustment
            ];

            $totalAmount += $amount;
        }

        if(isset($order->payment_gateway_reference) && $totalAmount > 0) {
            $paymentData = [
                'token' => $order->payment_gateway_reference,
                'metadata' => [
                    'Invoice' => $order->getId(),
                    'Order reference' => $order->getUuid(),
                    'Organisation' => $this->wallet->getOwner()->companyName(),
                    'Issuer' => $creator->getId(),
                    'Reason' => $reason ?? ''
                ]
            ];

            $response = $this->paymentGateway->refund($totalAmount, $paymentData);
        }

        if ( (isset($response) && $response->isSuccessful()) || empty($order->payment_gateway_reference)) {

            $partialRefund = $order->items->count() != count($items);
            $order->update([
                'order_status_id' => $this->models['order_status']::handleToId($partialRefund ? 'partial_refund' : 'refund')
            ]);

            foreach($refundedItems as $refundedItem) {
                $item            = $refundedItem['item'];
                $totalAmount     = $refundedItem['total_amount'];
                $totalVat        = $refundedItem['total_vat'];
                $tokenAdjustment = $refundedItem['token_adjustment'];

                $refundItem = $this->models['order_item_refund']::where('order_id', $order->getId())
                    ->where('order_item_id', $item->getId())->first();

                if(isset($refundItem)) {
                    continue;
                }

                $refundItem = $this->models['order_item_refund']::create([
                    'uuid' => Uuid::uuid4()->toString(),
                    'order_id' => $order->getId(),
                    'order_item_id' => $item->getId(),
                    'total_amount' => (-1 * abs($totalAmount)),
                    'total_vat' => $totalVat,
                    'tokens_adjustment' => $tokenAdjustment,
                    'reason' => $reason,
                    'payment_gateway_reference' => isset($response) ? $response->getReference() : null,
                    'payment_gateway_blob' => isset($response) ? json_encode($response->getData()) : null,
                    'created_user_id' => $creator->getId()
                ]);

                if($tokenAdjustment > 0) {
                    $this->wallet->debit($creator, $refundItem, $item);
                }
            }

            $this->events->fire('order.refunded', $order);

            return true;
        }

        return false;
    }
}
