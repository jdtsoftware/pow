<?php

namespace JDT\Pow\Interfaces;

interface Address {

    public function getId();
    public function getType();
    public function full();
}