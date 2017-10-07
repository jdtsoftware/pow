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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createAction()
    {
        try {
            $pow = app('pow');
            $order = $pow->createOrderFromBasket();
        } catch (\Exception $e) {
            return redirect()->route('products');
        }

        return redirect()->route('order-checkout', ['uuid' => $order->getUuid()]);
    }

    /**
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function checkoutAction($uuid)
    {
        $pow = app('pow');
        $order = $pow->order()->findByUuid($uuid);
        if($order->isComplete()) {
            return redirect()->route('order-view', [$uuid]);
        }

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
        $order = $pow->order()->findByUuid($uuid, \Auth::user());
        $response = $pow->payForOrder($order, Input::get());

        if($response->isSuccessful()) {
            return redirect()->route('order-view', [$order->getUuid()]);
        } else {
            //@todo not hard code strip specific things
            return view('pow::order.stripe-pay', [
                'publishable_key' => \Config::get('pow.stripe_options.publishable_key'),
                'order' => $order,
                'error' => $response->getMessage()
            ]);
        }

    }

    public function viewAction($uuid)
    {
        $pow = app('pow');
        if($pow->order()->validOrder($uuid)) {
            $order = $pow->order()->findByUuid($uuid, \Auth::user());
            return view('pow::order.complete', ['order' => $order]);
        } else {
            return redirect()->route('products');
        }
    }

    public function insufficientBalanceAction()
    {
        return view('pow::order.insufficient-balance');
    }
}
