<?php

namespace JDT\Pow\Interfaces\Entities;

interface WalletTokenType {

    public function getId();
    public function getHandle();
    public function getName();
    public function getDescription();
}