<?php

namespace JDT\Pow\Interfaces;

use \JDT\Pow\Interfaces\Basket as iBasket;
use \JDT\Pow\Interfaces\Wallet as iWallet;
use JDT\Pow\Interfaces\Entities\Order as iOrderEntity;

interface Order {
    public function findById(int $id) : iOrderEntity;
    public function findByUuid($uuid) : iOrderEntity;
    public function createFromBasket(iBasket $basket, IdentifiableId $creator) : iOrderEntity;
    public function pay(iOrderEntity $order, $paymentData = []);
    public function findEarliestRedeemableOrderItem();
}