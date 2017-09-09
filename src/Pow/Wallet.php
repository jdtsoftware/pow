<?php

namespace JDT\Pow;

use JDT\Pow\Interfaces\WalletOwner;

/**
 * Class Pow.
 */
class Wallet
{
    public function __construct(WalletOwner $walletOwner)
    {
        $this->models = Config::get('pow.models');
        $this->wallet = $this->models['wallet']::find($walletOwner->getWalletId());
    }

    public function balance($type)
    {
        return $this->wallet->token($type)->balance;
    }

    public function overdraft()
    {
        return $this->wallet->overdraft;
    }

    public function credit($tokens, $type, $linker)
    {

    }

    public function debit($tokens, $type, $linker)
    {

    }
}
