<?php

namespace JDT\Pow\Service;

use JDT\Pow\Interfaces\Shop as iShop;

/**
 * Class Shop.
 */
class Shop implements iShop
{
    protected $models;

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->models = \Config::get('pow.models');
    }

    /**
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\Paginator
     */
    public function list($page = 1, $perPage = 15) : \Illuminate\Pagination\Paginator
    {
        return $this->models['product_shop']::simplePaginate($perPage);
    }

    /**
     * @param $productShopId
     * @return Shop
     */
    public function findById($productShopId)
    {
        return $this->models['product_shop']::find($productShopId);
    }


}
