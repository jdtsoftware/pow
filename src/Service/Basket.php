<?php

namespace JDT\Pow\Service;

use Illuminate\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use JDT\Pow\Interfaces\Entities\Shop as iProductShopEntity;
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

    /**
     * Basket constructor.
     * @param SessionManager $session
     * @param Dispatcher $events
     */
    public function __construct(SessionManager $session, Dispatcher $events)
    {
        $this->session = $session;
        $this->events = $events;

        $this->models = \Config::get('pow.models');
        $this->classes = \Config::get('pow.classes');

        $this->vat = \Config::get('pow.vat');

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
     * @param iProductShopEntity $shopProduct
     * @param int $qty
     * @param boolean $qtyLocked
     *
     * @return $this
     */
    public function addProduct(iProductShopEntity $shopProduct, int $qty = 1, $qtyLocked = false)
    {
        if($qty > 0) {
            $this->basket = $this->session->get($this->instance);

            $product = $shopProduct->product;

            $unitPrice     = $product->getOriginalPrice();
            $originalPrice = $product->getOriginalPrice($qty);
            $adjustedPrice = $product->getAdjustedPrice($qty);
            $discount = $originalPrice - $adjustedPrice;

            $this->basket['products'][$shopProduct->getId()] = [
                'product' => $product,
                'product_shop' => $shopProduct,
                'qty' => $qty,
                'qty_locked' => $qtyLocked,
                'unit_price' => $unitPrice,
                'adjusted_price' => $adjustedPrice,
                'original_price' => $originalPrice,
                'discount' => $discount,
            ];

            if($product->orderForm) {
                $validation = [];
                foreach($product->orderForm as $input) {
                    $inputName = 'input['.$input->getId().']';

                    $form[$input->getId()] = [
                        'name' => $inputName,
                        'label' => $input->getName(),
                        'type' => $input->getType(),
                        'hidden' => $input->isHidden(),
                        'validation' => $input->getValidation(),
                    ];
                    $validation[$inputName] = $input->getValidation();
                }

                $this->basket['order_forms'][$shopProduct->getId()]['form'] = $form;
                $this->basket['order_forms'][$shopProduct->getId()]['validation'] = $validation;
            }

            $this->session->put($this->instance, $this->basket);
            $this->events->fire('basket.added', $product);
        } else {
            $this->basket = $this->session->get($this->instance);
            unset($this->basket[$shopProduct->getId()]);
            $this->session->put($this->instance, $this->basket);
            $this->events->fire('basket.removed', $shopProduct);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasOrderForms() : bool
    {
        $basket = $this->session->get($this->instance);
        return !empty($basket['order_forms']);
    }

    /**
     * @return array
     */
    public function getOrderForms() : array
    {
        $basket = $this->session->get($this->instance);
        return !empty($basket['order_forms']) ? $basket['order_forms'] : [];
    }

    /**
     * @param $request
     * @param $productShopId
     * @return bool
     */
    public function updateOrderForm($request, $productShopId)
    {
        $this->basket = $this->session->get($this->instance);

        $orderForms = $this->getOrderForms();
        $form = $orderForms[$productShopId]['form'];
        $validation = $orderForms[$productShopId]['validation'];

        $formData = [];

        foreach($form as $inputId => $input) {
            $formData[$input['name']] = ($input['type'] == 'file')
                ? $request->file($input['name'])
                : $request->get($input['name']);
        }

        if(\Validator::make($formData, $validation)->valid()) {
            $this->basket['order_forms'][$productShopId]['data'] = $formData;

            dd($formData);
            return true;
        }



        return false;
    }


    /**
     * @param iProductShopEntity $shopProduct
     * @return $this
     */
    public function removeProduct(iProductShopEntity $shopProduct)
    {
        $this->basket = $this->session->get($this->instance);
        unset($this->basket['products'][$shopProduct->getId()]);
        $this->session->put($this->instance, $this->basket);
        $this->events->fire('basket.removed', $shopProduct);

        return $this;
    }

    /**
     * @return $this
     */
    public function clearBasket()
    {
        $this->session->put($this->instance, null);
        return $this;
    }

    /**
     * @return array
     */
    public function getBasket()
    {
        return $this->session->get($this->instance);
    }

    /**
     * @param int $totalPrice
     * @return float|int
     */
    public function getVATCharge($totalPrice)
    {
        if(empty($this->vat) || empty($totalPrice)) {
            return 0;
        }

        return ($this->vat / 100) * $totalPrice;
    }

    /**
     * @return BasketPrices
     */
    public function getTotalPrices() : BasketPrices
    {
        $this->basket = $this->session->get($this->instance);
        if(empty($this->basket['products'])) {
            return 0;
        }

        $originalSubTotalPrice = 0;
        $totalDiscount = 0;
        foreach($this->basket['products'] as $product) {

            $originalTotalPrice = $product['product']->getOriginalPrice($product['qty']);
            $adjustedTotalPrice = $product['product']->getAdjustedPrice($product['qty']);
            $discount = $originalTotalPrice - $adjustedTotalPrice;

            $originalSubTotalPrice += $originalTotalPrice;
            $totalDiscount += $discount;
        }

        $prices = new BasketPrices();
        $prices->setOriginalSubTotalPrice($originalSubTotalPrice)
            ->setAdjustedSubTotalPrice($prices->originalSubTotalPrice - $totalDiscount)
            ->setOriginalVat($this->getVATCharge($prices->originalSubTotalPrice))
            ->setAdjustedVat($this->getVATCharge($prices->adjustedSubTotalPrice))
            ->setOriginalTotalPrice($prices->originalSubTotalPrice+$this->getVATCharge($prices->originalSubTotalPrice))
            ->setAdjustedTotalPrice($prices->adjustedSubTotalPrice+$this->getVATCharge($prices->adjustedSubTotalPrice))
            ->setDiscountPrice($totalDiscount > 0 ?  (-1 * abs($totalDiscount)) : null);

        $this->session->put($this->instance, $this->basket);

        return $prices;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        $basket = $this->getBasket();
        return empty($basket['products']);
    }
}
