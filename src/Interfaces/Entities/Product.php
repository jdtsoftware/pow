<?php

namespace JDT\Pow\Interfaces\Entities;

interface Product {

    public function token();


    public function getId();
    public function getOriginalPrice($qty = 0) : float;
    public function getAdjustedPrice($qty = 0) : float;
    public function getName();
    public function getDescription();
}