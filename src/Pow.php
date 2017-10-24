<?php

namespace JDT\Pow;

use Illuminate\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use JDT\Pow\Entities\Order\OrderStatus;
use JDT\Pow\Entities\Wallet\Wallet;
use JDT\Pow\Entities\WalletTokenType;
use JDT\Pow\Interfaces\Entities\OrderItem as iOrderItemEntity;
use JDT\Pow\Interfaces\Gateway;
use JDT\Pow\Interfaces\IdentifiableId;
use JDT\Pow\Interfaces\Redeemable;
use JDT\Pow\Interfaces\WalletOwner as iWalletOwner;
use JDT\Pow\Interfaces\Basket as iBasket;
use JDT\Pow\Interfaces\Wallet as iWallet;
use JDT\Pow\Interfaces\Product as iProduct;
use JDT\Pow\Interfaces\Order as iOrder;
use JDT\Pow\Interfaces\Shop as iShop;
use JDT\Pow\Interfaces\Entities\Order as iOrderEntity;
use Ramsey\Uuid\Uuid;

/**
 * Class Pow.
 */
class Pow
{
    protected $walletOwner;

    protected static $walletOwnerClosure;
    protected static $walletLookupClosure;

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

        $this->user = \Auth::user();

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
            default:
                throw new \RuntimeException('Unknown payment gateway - please set one in pow.php');
                break;
        }

    }

    /**
     * @param string $instance
     *
     * @return iBasket
     */
    public function basket($instance = null) : iBasket
    {
        return new $this->classes['basket']($this->session, $this->events, $this->wallet(), $instance);
    }

    /**
     * @return iProduct
     */
    public function product() : iProduct
    {
        return new $this->classes['product']($this->wallet());
    }

    /**
     * @return iOrder
     */
    public function order() : iOrder
    {
        return new $this->classes['order'](
            $this->paymentGateway,
            $this->wallet(),
            $this->events
        );
    }

    /**
     * @return iShop
     */
    public function shop() : iShop
    {
        return new $this->classes['shop']($this->wallet());
    }

    /**
     * $param string|null $basketInstance
     * @return iOrderEntity
     */
    public function createOrderFromBasket($basketInstance = null) : iOrderEntity
    {
        return $this->order()->createFromBasket($this->basket($basketInstance), $this->user);
    }

    /**
     * @param $order
     * @param $input
     * @return Gateway
     */
    public function payForOrder($order, $input) : Gateway
    {
        $response = $this->order()->pay($order, $input);

        if ($response->isSuccessful()) {
            $wallet = $this->wallet();
            foreach ($order->items as $orderItem) {
                $wallet->credit(
                    $this->user,
                    $orderItem,
                    $orderItem);
            }

            $order->update([
                'order_status_id' => $this->models['order_status']::handleToId('complete')
            ]);
        }

        return $response;
    }

    /**
     * @param $order
     * @param $status
     * @param bool $creditOrder
     */
    public function updateOrderStatus($order, $status, $creditOrder = false)
    {
        $orderUpdate = [
            'payment_gateway_id' => null,
            'order_status_id' => $this->models['order_status']::handleToId($status),
        ];

        if($creditOrder) {
            $orderUpdate = $orderUpdate + [
                    'adjusted_vat_price' => 0,
                    'adjusted_total_price' => 0,
            ];

            foreach($order->items as $item) {
                $item->update([
                    'adjusted_total_price' => 0,
                    'adjusted_vat_price' => 0,
                ]);
            }

        }

        if($status == 'complete') {
            $wallet = $this->wallet($order->wallet->getOwner());
            foreach ($order->items as $orderItem) {
                $wallet->credit(
                    $this->user,
                    $orderItem,
                    $orderItem);
            }
        }

        $order->update($orderUpdate);
    }

    /**
     * @param string $uuid
     * @param $items
     * @param $reason
     * @return mixed
     */
    public function refundOrder($uuid, $items, $reason = null)
    {
        $order = $this->order()->findByUuid($uuid);
        $this->setWalletOwner($order->wallet->getOwner());
        if(!$order) {
            return null;
        }

        return $this->order()->refund($order, $this->user, $items, $reason);
    }

    /**
     * @param IdentifiableId $redeemer
     * @param Redeemable $redeemableLinker
     * @param iOrderItemEntity|null $orderItemEntity
     * @throws \Exception
     */
    public function redeemToken(IdentifiableId $redeemer, Redeemable $redeemableLinker, iOrderItemEntity $orderItemEntity = null)
    {
        $tokenVaue = (int) $redeemableLinker->getTokenValue();
        if($tokenVaue < 0) {
            throw new \Exception('Token cost cannot be less than 0');
        }

        if(empty($orderItemEntity)) {
            $orderItemEntity = $this->order()->findEarliestRedeemableOrderItem($redeemableLinker->getTokenType()->getHandle());
        }

        $orderItemEntity->update([
            'tokens_spent' => $orderItemEntity->tokens_spent + $tokenVaue
        ]);

        $this->wallet()->debit($redeemer, $redeemableLinker, $orderItemEntity);
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
     * @return bool
     */
    public function hasWallet() : bool
    {
        return $this->wallet()->exists();
    }

    /**
     * @param $walletOwner
     */
    public function setWalletOwner(iWalletOwner $walletOwner)
    {
        $this->walletOwner = $walletOwner;
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
     * @return Collection
     */
    public function listWallets()
    {
        return $this->models['wallet']::all();
    }

    /**
     * @return Collection
     */
    public function walletTokenTypes()
    {
        return $this->models['wallet_token_type']::all();
    }

    /**
     * @param \Closure $closure
     */
    public static function setWalletOwnerClosure(\Closure $closure)
    {
        self::$walletOwnerClosure = $closure;
    }

    /**
     * @param \Closure $closure
     */
    public static function setWalletOwnerLookup(\Closure $closure)
    {
        self::$walletLookupClosure = $closure;
    }

    /**
     * @param $walletId
     * @return null
     */
    public static function getWalletOwner($walletId)
    {
        $lookup = self::$walletLookupClosure;
        return $lookup ? $lookup($walletId) : null;
    }
}
