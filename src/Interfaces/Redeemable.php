<?php

namespace JDT\Pow\Interfaces;

interface Redeemable {

    public function getTokenValue();
    public function getTokenType();
    public function getLinkerId();
    public function getLinkerType();
}