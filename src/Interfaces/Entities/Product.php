<?php

namespace JDT\Pow\Interfaces\Entities;

interface Product {

    public function token();


    public function getId();
    public function getTotalPrice($qty);
    public function getVATCharge($totalPrice);
    public function getName();
    public function getDescription();
}