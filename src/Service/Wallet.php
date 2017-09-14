<?php

namespace JDT\Pow\Service;

use JDT\Pow\Interfaces\WalletOwner;

/**
 * Class Pow.
 */
class Wallet implements \JDT\Pow\Interfaces\Wallet
{
    public function __construct(WalletOwner $walletOwner)
    {
        $this->models = \Config::get('pow.models');
        $this->wallet = $this->models['wallet']::find($walletOwner->getWalletId());
    }

    public function getId()
    {
        return $this->wallet->getId();
    }

    public function getUuid()
    {
        return $this->wallet->getUuid();
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
