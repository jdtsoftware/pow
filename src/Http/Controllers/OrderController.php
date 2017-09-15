<?php

namespace JDT\Pow\Http\Controllers;

use Illuminate\Support\Facades\Input;
use JDT\Api\Payload;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use JDT\Api\Contracts\ApiEndpoint;
use Illuminate\Routing\Controller as BaseController;
use Omnipay\Omnipay;

/**
 * Class OrderController
 * @package JDT\Pow\Http\Controllers
 */
class OrderController extends BaseController
{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createAction()
    {
        $pow = app('pow');
        $order = $pow->createOrderFromBasket();

        return redirect()->route('order-view', ['uuid' => $order->getUuid()]);
    }

    public function viewAction($uuid)
    {
        $pow = app('pow');
        $order = $pow->order()->findByUuid($uuid);

        return view('pow::order.stripe-pay', [
            'publishable_key' => \Config::get('pow.stripe_options.publishable_key'),
            'order' => $order,
        ]);

    }

    /**
     * @todo move to order service
     *
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function payAction($uuid)
    {
        $pow = app('pow');
        $order = $pow->order()->findByUuid($uuid);

        $gateway = Omnipay::create('Stripe');
        $gateway->setApiKey(\Config::get('pow.stripe_options.secret_key'));
        /*$response = $gateway->purchase([
            'currency'      => \Config::get('pow.stripe_options.currency'),
            'source'        => Input::get('stripeToken'),
            'amount'        => round($order->getTotalPrice(), 2),
        ])->send();

        $pow->basket()->clearBasket();
        $order->update([
            'payment_gateway_reference' => $response->getTransactionReference(),
            'payment_gateway_blob' => json_encode($response->getData())
        ]);*/

        if(/*$response->isSuccessful() || */1==1) {
            $wallet = $pow->wallet();
            foreach($order->items as $orderItem) {
                $product = $orderItem->product;
                $wallet->credit(
                    \Auth::user(),
                    $product->token->tokens,
                    $product->token->type,
                    $order,
                    $orderItem);
            }

            return redirect()->route('order-complete', [$order->getUuid()]);
        } else {
            return view('pow::order.stripe-pay', [
                'publishable_key' => \Config::get('pow.stripe_options.publishable_key'),
                'order' => $order,
                'error' => $response->getMessage()
            ]);
        }

    }

    public function completeAction($uuid)
    {
        $pow = app('pow');
        $order = $pow->order()->findByUuid($uuid);
        return view('pow::order.complete', ['order' => $order]);
    }

}
