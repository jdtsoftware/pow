<?php

namespace JDT\Pow\Interfaces\Entities;

use JDT\Pow\Interfaces\Entities\OrderItem as iOrderItemEntity;
use JDT\Pow\Interfaces\Entities\Product as iProductEntity;
use JDT\Pow\Interfaces\Entities\Shop as iProductShopEntity;

interface Order {

    public function getId();
    public function getUuid();
    public function getOriginalVATCharge();
    public function getAdjustedVATCharge();
    public function getOriginalPrice();
    public function getAdjustedPrice();
    public function addLineItem(iProductEntity $product, iProductShopEntity $productShop, int $qty = 1, $vatPercentage = null) : iOrderItemEntity;
    public function items();
}