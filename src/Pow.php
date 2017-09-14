<?php

namespace JDT\Pow;

use Illuminate\Events\Dispatcher;
use Illuminate\Session\SessionManager;
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
    protected static $walletOwner;

    /**
     * Pow constructor.
     */
    public function __construct(SessionManager $session, Dispatcher $events)
    {
        $this->session = $session;
        $this->events = $events;

        $this->models = \Config::get('pow.models');
        $this->classes = \Config::get('pow.classes');
        $this->closures = \Config::get('pow.closures');
    }

    /**
     * @return iBasket
     */
    public function basket() : iBasket
    {
        return new $this->classes['basket']($this->session, $this->events);
    }

    /**
     * @return iProduct
     */
    public function product() : iProduct
    {
        return new $this->classes['product'];
    }

    public function order()
    {
        return new $this->classes['order'];
    }

    public function createOrderFromBasket()
    {
        return $this->order()
            ->create($this->basket())
            ->checkout();
    }

    /**
     * @param iWalletOwner|null $walletOwner
     * @return iWallet
     */
    public function wallet(iWalletOwner $walletOwner = null) : iWallet
    {
        $walletOwner = $walletOwner ?? self::$walletOwner;
        if(is_callable($walletOwner)) {
            $walletOwner = $walletOwner();
        }

        if(is_null($walletOwner)) {
            throw new \RuntimeException('Cannot find default wallet - please provide or set one');
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

    /**
     * @param \Closure $closure
     */
    public static function setWalletOwner(\Closure $closure)
    {
        self::$walletOwner = $closure;
    }
}
