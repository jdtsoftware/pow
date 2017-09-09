<?php

namespace JDT\Pow;

use JDT\Pow\Interfaces\WalletOwner;

/**
 * Class Pow.
 */
class Pow
{
    private $classes;

    public function __construct()
    {
        $this->models = Config::get('pow.models');
        $this->basket = new $this->classes['basket'];
    }

    public function addProduct(\App\Modules\Address\Entities\Product $product)
    {
        $this->basket->addProduct($product);
        return $this;
    }

    public function checkout()
    {
        return $this->basket->checkout();
    }




    public function wallet(WalletOwner $walletOwner)
    {
        return new $this->classes['wallet']($walletOwner);
    }
}
