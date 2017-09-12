<?php

namespace JDT\Pow\Interfaces;

interface Basket {

    public function addProduct(\JDT\Pow\Interfaces\Entities\Product $product);
    public function getBasket();
    public function getTotalPrice();
}