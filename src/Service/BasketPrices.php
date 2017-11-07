<?php

namespace JDT\Pow\Service;

use Illuminate\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use \JDT\Pow\Interfaces\Entities\Product as iProductEntity;
use \JDT\Pow\Interfaces\Basket as iBasket;

/**
 * Class BasketPrices
 */
class BasketPrices
{
    public $originalSubTotalPrice;
    public $adjustedSubTotalPrice;
    public $originalTotalPrice;
    public $adjustedTotalPrice;
    public $discountPrice;
    public $originalVat;
    public $adjustedVat;

    public function setOriginalSubTotalPrice($originalSubTotalPrice)
    {
        $this->originalSubTotalPrice = $originalSubTotalPrice;
        return $this;
    }

    public function setAdjustedSubTotalPrice($adjustedSubTotalPrice)
    {
        $this->adjustedSubTotalPrice = $adjustedSubTotalPrice;
        return $this;
    }

    public function setOriginalTotalPrice($originalTotalPrice)
    {
        $this->originalTotalPrice = $originalTotalPrice;
        return $this;
    }

    public function setAdjustedTotalPrice($adjustedTotalPrice)
    {
        $this->adjustedTotalPrice = $adjustedTotalPrice;
        return $this;
    }

    public function setDiscountPrice($discountPrice)
    {
        $this->discountPrice = $discountPrice;
        return $this;
    }

    public function setOriginalVat($orignalVat)
    {
        $this->originalVat = $orignalVat;
        return $this;
    }

    public function setAdjustedVat($adjustedVat)
    {
        $this->adjustedVat = $adjustedVat;
        return $this;
    }

    public function totalPrice()
    {
        return $this->adjustedTotalPrice + $this->adjustedVat;
    }

}
