<?php

namespace JDT\Pow\Service\Gateway;

use JDT\Pow\Interfaces\Gateway as iGateway;
use Omnipay\Omnipay;

/**
 * Class Stripe.
 */
class Stripe implements iGateway
{
    protected $gateway;

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->models = \Config::get('pow.models');
        $this->gateway = Omnipay::create('Stripe');
        $this->gateway->setApiKey(\Config::get('pow.stripe_options.secret_key'));
    }

    /**
     * @param float $totalPrice
     * @param array $paymentData
     * @return iGateway
     */
    public function pay(float $totalPrice, array $paymentData = []) : iGateway
    {
        $this->response = $this->gateway->purchase([
            'currency' => \Config::get('pow.stripe_options.currency'),
            'source' => $paymentData['stripeToken'],
            'amount' => round($totalPrice, 2),
        ])->send();

        return $this;
    }

    /**
     * @param float $totalPrice
     * @param array $paymentData
     * @return iGateway
     */
    public function refund(float $totalPrice, array $paymentData = []) : iGateway
    {
        $transaction = $this->gateway->refund([
            'transactionReference' => $paymentData['token'],
            'amount' => round($totalPrice, 2),
        ]);

        $this->response = $transaction->send();

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isSuccessful()
    {
        return $this->response ? $this->response->isSuccessful() : null;
    }

    /**
     * @return null
     */
    public function getReference()
    {
        return $this->response ? $this->response->getTransactionReference() : null;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->response ? $this->response->getMessage() : null;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->response ? $this->response->getData() : null;
    }
}
