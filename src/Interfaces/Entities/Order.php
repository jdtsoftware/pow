<?php

namespace JDT\Pow\Interfaces\Entities;

interface Order {

    public function getId();
    public function getUuid();
    public function addLineItem(iProductEntity $product, int $qty = 1) : iOrderItemEntity;
}