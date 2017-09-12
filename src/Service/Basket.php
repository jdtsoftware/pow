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

    protected $basket;
    protected $instance;

    private $models;
    private $classes;

    public function __construct(SessionManager $session, Dispatcher $events)
    {
        $this->basket = $session;
        $this->events = $events;

        $this->models = \Config::get('pow.models');
        $this->classes = \Config::get('pow.classes');

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
        if(empty($qty)) {
            unset($this->basket[$product->getId()]);
        } else {
            $basket = $this->basket->get($this->instance);

            $basket[$product->id] = [
                'product' => $product,
                'qty' => $qty,
                'unit_price' => $product->getTotalPrice(),
                'total_price' => $product->getTotalPrice($qty)
            ];

            $this->basket->put($this->instance, $basket);
            $this->events->fire('basket.added', $product);
        }

        return $this;
    }

    public function removeProduct(iProductEntity $product)
    {
        $basket = $this->basket->get($this->instance);
        unset($basket[$product->getId()]);
        $this->basket->put($this->instance, $basket);
        $this->events->fire('basket.added', $product);

        return $this;
    }

    public function clearBasket()
    {
        $this->basket->put($this->instance, null);
        return $this;
    }

    public function getBasket()
    {
        return $this->basket->get($this->instance);
    }

    public function checkout()
    {
        return $this->classes['order']::create($this);
    }

    public function getTotalPrice()
    {
        $basket = $this->basket->get($this->instance);

        $totalPrice = 0;
        foreach($basket as $item)
        {
            $item['product']['total_price'] = $item['product']['unit_price'] * $item['qty'];
            $this->totalPrice += $item['product']['total_price'];
        }

        return $totalPrice;
    }

    public function isEmpty()
    {
        return empty($this->getBasket());
    }
}
