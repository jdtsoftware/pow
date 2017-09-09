<?php

namespace JDT\Pow\Interfaces;

interface Wallet {

    public function balance($type);
    public function overdraft();
    public function credit($tokens, $type, $linker);
    public function debit($tokens, $type, $linker);
}