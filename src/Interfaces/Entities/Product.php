<?php

namespace JDT\Pow\Interfaces\Entities;

interface Product {

    public function getId();
    public function getTotalPrice($qty);
    public function getName();
    public function getDescription();
}