<?php

namespace JDT\Pow\Interfaces\Entities;

interface Product {

    public function getId();
    public function getTotalValue();
    public function getName();
    public function getDescription();
}