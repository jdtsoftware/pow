<?php

namespace JDT\Pow;

use Illuminate\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use JDT\Pow\Entities\WalletTokenType;
use JDT\Pow\Interfaces\Gateway;
use JDT\Pow\Interfaces\WalletOwner as iWalletOwner;
use JDT\Pow\Interfaces\Basket as iBasket;
use JDT\Pow\Interfaces\Wallet as iWallet;
use JDT\Pow\Interfaces\Product as iProduct;
use JDT\Pow\Interfaces\Order as iOrder;
use JDT\Pow\Interfaces\Entities\Order as iOrderEntity;

/**
 * Class Pow.
 */
class Pow
{
    protected $walletOwner;

    protected static $walletOwnerClosure;

    private $classes;

    /**
     * Pow constructor.
     */
    public function __construct(SessionManager $session, Dispatcher $events, $walletOwner = null)
    {
        $this->session = $session;
        $this->events = $events;

        $this->models = \Config::get('pow.models');
        $this->classes = \Config::get('pow.classes');
        $this->closures = \Config::get('pow.closures');
        $this->gateways = \Config::get('pow.gateways');

        $walletOwner = $walletOwner ?? self::$walletOwnerClosure;
        if(is_callable($walletOwner)) {
            $walletOwner = $walletOwner();
        }

        $this->walletOwner = $walletOwner;

        if(is_null($this->walletOwner)) {
            throw new \RuntimeException('Cannot find default wallet - please provide or set one');
        }

        switch(\Config::get('pow.payment_gateway')) {
            case 'stripe':
                    $this->paymentGateway = new $this->gateways['stripe'];
                break;
        }
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

    /**
     * @return mixed
     */
    public function order() : iOrder
    {
        return new $this->classes['order']($this->paymentGateway);
    }

    /**
     * @param iWallet|null $wallet
     * @return iOrderEntity
     */
    public function createOrderFromBasket(iWallet $wallet = null) : iOrderEntity
    {
        return $this->order()->createFromBasket($this->wallet($wallet), $this->basket());
    }

    /**
     * @param $order
     * @param $input
     * @param iWallet|null $wallet
     * @return Gateway
     */
    public function payForOrder($order, $input, iWallet $wallet = null) : Gateway
    {
        $response = $this->order()->pay($order, $input);

        if ($response->isSuccessful()) {
            $wallet = $wallet ?? $this->wallet();
            foreach ($order->items as $orderItem) {
                $product = $orderItem->product;
                $wallet->credit(
                    \Auth::user(),
                    $product->token->tokens,
                    $product->token->type,
                    $order,
                    $orderItem);
            }
        }

        return $response;
    }

    /**
     * @param iWalletOwner|null $walletOwner
     * @return iWallet
     */
    public function wallet(iWalletOwner $walletOwner = null) : iWallet
    {
        $walletOwner = $walletOwner ?? $this->walletOwner;
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
    public static function setWalletOwnerClosure(\Closure $closure)
    {
        self::$walletOwnerClosure = $closure;
    }
}
