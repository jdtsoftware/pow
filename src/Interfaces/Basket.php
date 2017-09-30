<?php

namespace JDT\Pow\Interfaces;

interface Basket {

    public function addProduct(\JDT\Pow\Interfaces\Entities\Shop $shopProduct);
    public function getBasket();
    public function clearBasket();
    public function getTotalPrices();
}