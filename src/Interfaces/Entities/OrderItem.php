<?php

namespace JDT\Pow\Interfaces\Entities;

interface OrderItem {

    public function getId();
    public function product();
    public function getTotalPrice();
}