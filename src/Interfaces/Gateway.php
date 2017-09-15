<?php

namespace JDT\Pow\Interfaces;

interface Gateway {

    public function pay(float $totalPrice, array $paymentData = []);
    public function isSuccessful();
    public function getReference();
    public function getMessage();
    public function getData();
}