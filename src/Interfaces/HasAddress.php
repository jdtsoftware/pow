<?php

namespace JDT\Pow\Interfaces;

interface HasAddress {

    public function hasAddress();
    public function getAddress() : \JDT\Pow\Interfaces\Address;
}