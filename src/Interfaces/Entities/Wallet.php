<?php

namespace JDT\Pow\Interfaces\Entities;

interface Wallet {

    public function getId();
    public function getUuid();
    public function getOverdraft();
}