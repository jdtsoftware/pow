<?php

namespace JDT\Pow\Service;

use Illuminate\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;
use JDT\Pow\Interfaces\Entities\Shop as iProductShopEntity;
use JDT\Pow\Interfaces\Entities\Product as iProductEntity;
use \JDT\Pow\Interfaces\Basket as iBasket;
use \JDT\Pow\Interfaces\Wallet as iWallet;
use JDT\Pow\Traits\VatCharge;

/**
 * Class Basket.
 * @info https://github.com/Crinsane/LaravelShoppingcart/
 */
class Basket implements iBasket
{
    use VatCharge;

    const DEFAULT_INSTANCE = 'default';

    protected $session;
    protected $instance;

    private $models;
    private $classes;

    private $basket;

    /**
     * Basket constructor.
     *
     * @param SessionManager $session
     * @param Dispatcher $events
     * @param iWallet $wallet
     * @param string $instance
     */
    public function __construct(SessionManager $session, Dispatcher $events, iWallet $wallet, $instance = null)
    {
        $this->session = $session;
        $this->events = $events;
        $this->wallet = $wallet;

        $this->models = \Config::get('pow.models');
        $this->classes = \Config::get('pow.classes');

        $this->vat = $this->wallet->getVatPerecentage();

        $this->instance($instance);
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
     * @return bool
     */
    public function isInstance($instance)
    {
        return $this>instance == $instance;
    }

    /**
     * @param iProductEntity $product
     * @param int $qty
     * @param null|iProductShopEntity $productShop
     *
     * @return $this
     */
    public function addProduct(iProductEntity $product, int $qty = 1, iProductShopEntity $productShop = null)
    {
        $basketId = $product->getId();

        if($qty > 0) {
            $this->basket = $this->session->get($this->instance);

            $qtyLocked = isset($productShop) ? $productShop->quantity_lock : false;
            if(isset($this->basket['products'][$basketId]) && $qtyLocked == false) {
                $qty = $this->basket['products'][$basketId]['qty'] + $qty;
            }

            $unitPrice     = $product->getOriginalPrice();
            $originalPrice = $product->getOriginalPrice($qty);
            $adjustedPrice = $product->getAdjustedPrice($qty);
            $discount = $originalPrice - $adjustedPrice;

            $this->basket['products'][$basketId] = [
                'product' => $product,
                'product_shop' => $productShop,
                'qty' => $qty,
                'qty_locked' => $qtyLocked,
                'unit_price' => $unitPrice,
                'adjusted_price' => $adjustedPrice,
                'original_price' => $originalPrice,
                'discount' => $discount,
                'vat_percentage' => $product->config('vat_percentage')
            ];

            if($product->orderForm instanceof Collection && $product->orderForm->count() > 0) {
                $messages = [
                    'required' => 'This field is required',
                    'date' => 'Enter a valid date',
                    'image' => 'The file must be an image (jpeg, png, bmp, gif, or svg)',
                    'url' => 'Please enter a valid URL. (http://www.google.com)'
                ];

                $validation = [];
                foreach($product->orderForm as $input) {
                    $inputName = 'input_'.$input->getId();

                    $form[$input->getId()] = [
                        'name' => $inputName,
                        'description' => $input->getDescription(),
                        'label' => $input->getName(),
                        'type' => $input->getType(),
                        'hidden' => $input->isHidden(),
                        'validation' => $input->getValidation(),
                        'messages' => $messages
                    ];

                    $validation[$inputName] = $input->getValidation();

                    if($input->getType() == 'file') {
                        $uploadedName = 'file_'.$input->getId();

                        $form[$input->getId()]['uploaded_name'] = $uploadedName;
                        $validation[$inputName] =
                            str_replace(
                                'required',
                                'required_without:'.$uploadedName,
                                $validation[$inputName]
                            );  //hacky hacky

                        $validation[$uploadedName] = 'string';
                    }
                }

                $this->basket['order_forms'][$basketId]['form'] = $form;
                $this->basket['order_forms'][$basketId]['validation'] = $validation;
                $this->basket['order_forms'][$basketId]['messages'] = $messages;
            }

            $this->session->put($this->instance, $this->basket);
            $this->events->fire('basket.added', $product);
        } else {
            $this->basket = $this->session->get($this->instance);
            unset($this->basket['products'][$basketId]);
            unset($this->basket['order_forms'][$basketId]);
            $this->session->put($this->instance, $this->basket);
            $this->events->fire('basket.removed', $product);
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
     * @param $basketId
     * @return bool
     */
    public function updateOrderForm($request, $basketId)
    {
        $this->basket = $this->session->get($this->instance);

        $orderForms = $this->getOrderForms();
        $form = $orderForms[$basketId]['form'];
        $validation = $orderForms[$basketId]['validation'];

        $formData = [];

        foreach($form as $inputId => $input) {

            $inputName = $input['name'];
            if($input['type'] == 'file') {
                $inputName = $input['uploaded_name'];
            }

            $formData[$inputName] = $request->get($inputName);
        }

        if(\Validator::make($formData, $validation)->valid()) {
            $this->basket['order_forms'][$basketId]['data'] = $formData;
            $this->session->put($this->instance, $this->basket);
            return true;
        }

        return false;
    }


    /**
     * @param integer $basketId
     * @return $this
     */
    public function removeProduct($basketId)
    {
        $this->basket = $this->session->get($this->instance);
        if(isset($this->basket['products'][$basketId])) {

            $product = $this->basket['products'][$basketId]['product'];
            unset($this->basket['products'][$basketId]);
            unset($this->basket['order_forms'][$basketId]);
            $this->session->put($this->instance, $this->basket);

            $this->events->fire('basket.removed', $product);
        }

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
        $originalVatCharge = 0;
        $adjustedVatCharge = 0;
        foreach($this->basket['products'] as $product) {
            $productVat = isset($product['vat_percentage']) ? $product['vat_percentage'] : $this->vat;

            $originalTotalPrice = $product['product']->getOriginalPrice($product['qty']);
            $adjustedTotalPrice = $product['product']->getAdjustedPrice($product['qty']);
            $discount = $originalTotalPrice - $adjustedTotalPrice;

            $originalSubTotalPrice += $originalTotalPrice;
            $totalDiscount += $discount;
            $originalVatCharge += $this->getVatCharge($originalTotalPrice, $productVat);
            $adjustedVatCharge += $this->getVatCharge($adjustedTotalPrice, $productVat);
        }

        $prices = new BasketPrices();
        $prices->setOriginalSubTotalPrice($originalSubTotalPrice)
            ->setAdjustedSubTotalPrice($prices->originalSubTotalPrice - $totalDiscount)
            ->setOriginalVat($originalVatCharge)
            ->setAdjustedVat($adjustedVatCharge)
            ->setOriginalTotalPrice($prices->originalSubTotalPrice)
            ->setAdjustedTotalPrice($prices->adjustedSubTotalPrice)
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
