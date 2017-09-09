<?php

namespace JDT\Pow\Http\Controllers;

use JDT\Api\Payload;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use JDT\Api\Contracts\ApiEndpoint;
use Illuminate\Routing\Controller as BaseController;

class BasketController extends BaseController
{
    public function indexAction()
    {
        $pow = app('pow');
        $product = $pow->product()->findById(1);

        var_dump($product);

        $basket = $pow->basket();
        $basket->addProduct($product, 2);

        var_dump($basket);

        return view('pow::wallet.index');
    }

    public function checkoutAction()
    {

    }

    public function payAction()
    {

    }
}
