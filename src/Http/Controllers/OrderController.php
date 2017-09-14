<?php

namespace JDT\Pow\Http\Controllers;

use JDT\Api\Payload;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use JDT\Api\Contracts\ApiEndpoint;
use Illuminate\Routing\Controller as BaseController;

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
        $order = $pow->checkout();


    }

    public function viewAction()
    {

        return view('pow::order.stripe-pay', [
            'publishable_key' => \Config::get('pow.stripe_options.publishable_key'),
            'total_price' => $order->adjusted_total_price,
        ]);

    }

    public function payAction()
    {


    }

}
