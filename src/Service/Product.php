<?php

namespace JDT\Pow\Service;

use JDT\Pow\Interfaces\Entities\Product as iProductEntity;
use JDT\Pow\Interfaces\Product as iProduct;

/**
 * Class Pow.
 */
class Product implements iProduct
{
    public function __construct()
    {
        $this->models = \Config::get('pow.models');
    }

    public function findById($productId) : iProductEntity
    {
        return $this->models['product']::find($productId);
    }

    public function list($page, $perPage = 15)
    {
        return $this->models['product']::simplePaginate($perPage);
    }
}
