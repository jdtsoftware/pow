<?php

namespace JDT\Pow\Interfaces\Entities;

interface Product {

    public function getId();
    public function getTotalPrice();
    public function getName();
    public function getDescription();
}