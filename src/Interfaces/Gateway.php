<?php

namespace JDT\Pow\Interfaces;

interface Gateway {

    public function pay(float $totalPrice, array $paymentData = []) : Gateway;
    public function refund(float $totalPrice, array $paymentData = []) : Gateway;
    public function alreadyPaid() : Gateway;
    public function isSuccessful();
    public function getReference();
    public function getMessage();
    public function getData();
}