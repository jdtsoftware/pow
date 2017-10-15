<?php

namespace JDT\Pow\Service;

use JDT\Pow\Http\Requests\SaveProductRequest;
use JDT\Pow\Interfaces\Entities\Product as iProductEntity;
use JDT\Pow\Interfaces\Product as iProduct;

/**
 * Class Product.
 */
class Product implements iProduct
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

    /**
     * @return Collection
     */
    public function listAll()
    {
        return $this->models['product']::all();
    }

    /**
     * @param SaveProductRequest $requestData
     * @param iProductEntity|null $product
     * @return iProductEntity
     */
    public function updateOrCreate(SaveProductRequest $requestData, iProductEntity $product = null) : iProductEntity
    {
        $data = [
            'name' => $requestData->name,
            'description' => $requestData->description,
            'total_price' => $requestData->price,
        ];

        if(!empty($product)) {
            $product->update($data);
        } else {
            $product = $this->models['product']::create($data);
        }

        $product->updateToken($requestData);
        $product->updateAdjustment($requestData);
        $product->updateShop($requestData);

        return $product;
    }

}
