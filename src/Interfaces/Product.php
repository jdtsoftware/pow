<?php

namespace JDT\Pow\Interfaces;

use JDT\Pow\Interfaces\Entities\Product as iProductEntity;

interface Product {

    public function findById($productId) : iProductEntity;
    public function list($page, $perPage = 15) :\ Illuminate\Pagination\Paginator;
}