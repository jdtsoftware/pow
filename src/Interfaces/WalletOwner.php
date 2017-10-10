<?php

namespace JDT\Pow\Interfaces;

interface WalletOwner {

    public function getWalletId();
    public function getVatPerecentage();
    public function isVatExempt();
    public function companyName();
    public function getAddress() : \JDT\Pow\Interfaces\Address;
}