<?php

namespace JDT\Pow\Service;

use Illuminate\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use \JDT\Pow\Interfaces\Entities\Product as iProductEntity;
use \JDT\Pow\Interfaces\Basket as iBasket;

/**
 * Class Basket.
 * @info https://github.com/Crinsane/LaravelShoppingcart/
 */
class Basket implements iBasket
{
    const DEFAULT_INSTANCE = 'default';

    protected $session;
    protected $instance;

    private $models;
    private $classes;

    private $basket;

    public function __construct(SessionManager $session, Dispatcher $events)
    {
        $this->session = $session;
        $this->events = $events;

        $this->models = \Config::get('pow.models');
        $this->classes = \Config::get('pow.classes');

        $this->vat = \Config::get('pow.vat');
        $this->locale = \Config::get('pow.locale');
        $this->money_format = \Config::get('pow.money_format');

        setlocale(LC_MONETARY, $this->locale);

        $this->instance(self::DEFAULT_INSTANCE);
    }

    /**
     * Set the current cart instance.
     *
     * @param string|null $instance
     * @return Basket
     */
    public function instance($instance = null)
    {
        $instance = $instance ?: self::DEFAULT_INSTANCE;
        $this->instance = sprintf('%s.%s', 'basket', $instance);
        return $this;
    }

    /**
     * @param iProductEntity $product
     * @param int $qty
     * @return $this
     */
    public function addProduct(iProductEntity $product, int $qty = 1)
    {
        if($qty > 0) {
            $this->basket = $this->session->get($this->instance);

            $this->basket['products'][$product->id] = [
                'product' => $product,
                'qty' => $qty,
                'unit_price' => $this->format($product->getTotalPrice()),
                'total_price' => $this->format($product->getTotalPrice($qty))
            ];
            
            $this->session->put($this->instance, $this->basket);
            $this->events->fire('basket.added', $product);
        } else {
            $this->basket = $this->session->get($this->instance);
            unset($this->basket[$product->getId()]);
            $this->session->put($this->instance, $this->basket);
            $this->events->fire('basket.added', $product);
        }

        return $this;
    }

    public function removeProduct(iProductEntity $product)
    {
        $this->basket = $this->session->get($this->instance);
        unset($this->basket['products'][$product->getId()]);
        $this->session->put($this->instance, $this->basket);
        $this->events->fire('basket.added', $product);

        return $this;
    }

    public function clearBasket()
    {
        $this->session->put($this->instance, null);
        return $this;
    }

    public function getBasket()
    {
        return $this->session->get($this->instance);
    }

    public function checkout()
    {
        return $this->classes['order']::create($this);
    }

    public function getVATCharge(int $totalPrice)
    {
        if(empty($this->vat) || empty($totalPrice)) {
            return 0;
        }

        return ($this->vat / 100) * $totalPrice;
    }

    public function getTotalPrices()
    {
        $this->basket = $this->session->get($this->instance);
        if(empty($this->basket['products'])) {
            return 0;
        }

        $totalPrice = 0;
        $basketTotalPrice = 0;
        foreach($this->basket['products'] as $product) {

            $totalPrice = $product['product']->getTotalPrice($product['qty']);
            $product['total_price'] = $this->format($totalPrice);

            $basketTotalPrice += $totalPrice;
        }

        $this->basket['totals']['sub_total_price'] = $this->format($basketTotalPrice);
        $this->basket['totals']['vat_price'] = $this->format($this->getVATCharge($basketTotalPrice));
        $this->basket['totals']['total_price'] = $this->format($totalPrice+$this->getVATCharge($basketTotalPrice));

        $this->session->put($this->instance, $this->basket);

        return $this->basket['totals'];
    }

    public function isEmpty()
    {
        $basket = $this->getBasket();
        return empty($basket['products']);
    }

    public function format($number)
    {
        return money_format($this->money_format, $number);
    }
}
