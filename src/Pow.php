<?php

namespace JDT\Pow;

use JDT\Pow\Entities\WalletTokenType;
use JDT\Pow\Interfaces\WalletOwner as iWalletOwner;
use JDT\Pow\Interfaces\Basket as iBasket;
use JDT\Pow\Interfaces\Wallet as iWallet;
use JDT\Pow\Interfaces\Product as iProduct;

/**
 * Class Pow.
 */
class Pow
{
    private $classes;

    /**
     * Pow constructor.
     */
    public function __construct()
    {
        $this->models = \Config::get('pow.models');
        $this->classes = \Config::get('pow.classes');
        $this->closures = \Config::get('pow.closures');
    }

    /**
     * @return iBasket
     */
    public function basket() : iBasket
    {
        return new $this->classes['basket'];
    }

    /**
     * @return iProduct
     */
    public function product() : iProduct
    {
        return new $this->classes['product'];
    }

    /**
     * @param iWalletOwner|null $walletOwner
     * @return iWallet
     */
    public function wallet(iWalletOwner $walletOwner = null) : iWallet
    {
        $walletOwner = $walletOwner ?: (is_callable($this->closures['wallet_owner']) ? $this->closures['wallet_owner']() : null );
        if(is_null($walletOwner)) {
            throw new \RuntimeException('Cannot find default wallet - please provide one or set pow.closures.wallet_owner');
        }

        return new $this->classes['wallet']($walletOwner);
    }

    /**
     * @param $handle
     * @param $name
     * @param $description
     *
     * @return WalletTokenType
     */
    public function createWalletTokenType($handle, $name, $description) : WalletTokenType
    {
        return $this->models['wallet_token_type']::create([
            'handle' => $handle,
            'name' => $name,
            'description' => $description
        ]);
    }

    /**
     * @param int $overdraft
     * @return iWallet
     */
    public function createWallet($overdraft = 0) : iWallet
    {
        return $this->models['wallet']::create([
            'overdraft' => (int) $overdraft
        ]);
    }
}
