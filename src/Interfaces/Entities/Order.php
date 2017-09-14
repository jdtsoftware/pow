<?php

namespace JDT\Pow\Interfaces\Entities;

interface Order {

    public function getId();
    public function addLineItem();
}