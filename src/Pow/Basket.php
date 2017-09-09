<?php

namespace JDT\Pow;

/**
 * Class Basket.
 */
class Basket
{
    protected $basket;
    protected $totalPrice;

    public function __construct()
    {
        $this->models = Config::get('pow.models');
    }

    public function addProduct(Product $product, $qty = 1)
    {
        if(empty($qty)) {
            unset($this->basket[$product->id]);
        } else {
            $this->totalPrice += $product->price;
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
