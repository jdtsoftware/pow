<?php

namespace JDT\Pow\Service;

use \JDT\Pow\Interfaces\Entities\Product as iProductEntity;
use \JDT\Pow\Interfaces\Basket as iBasket;

/**
 * Class Basket.
 */
class Basket implements iBasket
{
    protected $basket;
    protected $totalPrice;

    public function __construct()
    {
        $this->models = \Config::get('pow.models');
    }

    public function addProduct(iProductEntity $product, int $qty = 1)
    {
        if(empty($qty)) {
            unset($this->basket[$product->getId()]);
        } else {
            $this->totalPrice += $product->getTotalPrice() * $qty;
            $this->basket[$product->id] = [
                'product' => $product,
                'qty' => $qty,
            ];
        }
    }

    public function clearBasket()
    {
        unset($this->totalPrice);
        unset($this->basket);
    }

    public function checkout()
    {
        return $this->basket;
    }

    public function totalPrice()
    {
        return $this->totalPrice;
    }
}
