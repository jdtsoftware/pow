<?php

namespace JDT\Pow\Traits;

trait VatCharge {

    public function getVatCharge($totalPrice, $vatPercentage = null)
    {
        if(empty($vatPercentage) || empty($totalPrice)) {
            return 0;
        }

        return ($totalPrice / 100) * $vatPercentage;
    }
}