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
    protected $response;

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
            'metadata' => $paymentData['metadata'] ?? []
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
            'metadata' => $paymentData['metadata'] ?? []
        ]);

        $this->response = $transaction->send();

        return $this;
    }

    /**
     * @return iGateway
     */
    public function alreadyPaid() : iGateway
    {
        $this->alreadyPaid = true;
        $this->data['error']['message'] = 'Order already paid for.';
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isSuccessful()
    {
        $this->data = isset($this->data) ? $this->data :
            (isset($this->response) ? $this->response->getData() : []);

        if(isset($this->data['error']['message']) && strstr($this->data['error']['message'], 'has already been refunded.')) {
            return true;
        }

        return $this->response ? $this->response->isSuccessful() : null;
    }

    public function isAlreadyPaid()
    {

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
        return isset($this->data['error']['message']) ? $this->data['error']['message'] :
            ($this->response ? $this->response->getMessage() : null);
    }

    /**
     * @return string
     */
    public function getData()
    {
        return isset($this->data) ? $this->data :
            ($this->response ? $this->response->getData() : null);
    }
}
