<?php

namespace JDT\Pow\Interfaces\Entities;

use JDT\Pow\Interfaces\Entities\OrderItem as iOrderItemEntity;
use JDT\Pow\Interfaces\Entities\Product as iProductEntity;

interface Order {

    public function getId();
    public function getUuid();
    public function getVATRate();
    public function getVATCharge();
    public function getOriginalPrice();
    public function getAdjustedPrice();
    public function addLineItem(iProductEntity $product, int $qty = 1) : iOrderItemEntity;
    public function items();
}