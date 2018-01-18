<?php

namespace JDT\Pow\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Routing\Controller as BaseController;

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
            throw $e;
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
        if($pow->order()->validOrder($uuid, true) === false) {
            return redirect()->route('wallet');
        }

        if($order->isComplete() || $order->isRefunded()) {
            return redirect()->route('order-view', [$uuid]);
        }

        return view('pow::order.stripe-pay', [
            'publishable_key' => \Config::get('pow.stripe_options.publishable_key'),
            'order' => $order,
            'wallet_owner' => $pow->wallet()->getOwner()
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
        if($pow->order()->validOrder($uuid, true) === false) {
            return redirect()->route('wallet');
        }

        $order = $pow->order()->findByUuid($uuid, true);
        $response = $pow->payForOrder($order, Input::get());

        if(isset($response) && $response->isSuccessful()) {
            return redirect()->route('order-view', [$order->getUuid()]);
        } else {
            //@todo not hard code strip specific things
            return view('pow::order.stripe-pay', [
                'publishable_key' => \Config::get('pow.stripe_options.publishable_key'),
                'order' => $order,
                'error' => isset($response) ? $response->getMessage() : null
            ]);
        }

    }

    /**
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function viewAction($uuid)
    {
        $pow = app('pow');
        if($pow->order()->validOrder($uuid, true) === false) {
            return redirect()->route('wallet');
        }

        $order = $pow->order()->findByUuid($uuid, true);
        return view(
            'pow::order.complete',
            [
                'order' => $order,
                'wallet_owner' => $pow->wallet()->getOwner()
            ]
        );

    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function insufficientBalanceAction()
    {
        return view('pow::order.insufficient-balance');
    }

    /**
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function downloadInvoiceAction($uuid)
    {
        $pow = app('pow');
        if($pow->order()->validOrder($uuid)) {
            $order = $pow->order()->findByUuid($uuid, true);

            $pdf = \PDF::loadView(
                'pow::order.invoice',
                [
                    'order' => $order,
                    'wallet_owner' => $pow->wallet()->getOwner(),
                    'public_local_path' => public_path()
                ]
            );
            return $pdf->download('invoice-'.$order->id.'.pdf');
        } else {
            return redirect()->route('products');
        }
    }
}
