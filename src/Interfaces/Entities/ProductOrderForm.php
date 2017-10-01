<?php

namespace JDT\Pow\Interfaces\Entities;

interface ProductOrderForm {

    public function isValidType($type);
    public function getType(): string;
    public function getValidation(): string;
    public function getName(): string;
    public function isHidden(): bool;
}