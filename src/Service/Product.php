<?php

namespace JDT\Pow\Service;

use JDT\Pow\Interfaces\Entities\Product as iProductEntity;
use JDT\Pow\Interfaces\Product as iProduct;

/**
 * Class Product.
 */
class Product implements iProduct
{
    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->models = \Config::get('pow.models');
    }

    /**
     * @param $productId
     * @return iProductEntity
     */
    public function findById($productId) : iProductEntity
    {
        return $this->models['product']::find($productId);
    }

    /**
     * @param $page
     * @param int $perPage
     * @return \Illuminate\Pagination\Paginator
     */
    public function list($page, $perPage = 15) : \Illuminate\Pagination\Paginator
    {
        return $this->models['product']::simplePaginate($perPage);
    }
}
