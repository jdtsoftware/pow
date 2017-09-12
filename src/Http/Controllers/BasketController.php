<?php

namespace JDT\Pow\Http\Controllers;

use JDT\Api\Payload;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use JDT\Api\Contracts\ApiEndpoint;
use Illuminate\Routing\Controller as BaseController;

/**
 * Class BasketController
 * @package JDT\Pow\Http\Controllers
 */
class BasketController extends BaseController
{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function indexAction()
    {
        $pow = app('pow');
        if($pow->basket()->isEmpty()) {
            return redirect()->route('products');
        }

        $basket = $pow->basket()->getBasket();
        return view('pow::basket.index', ['basket' => $basket]);
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function addProductAction(Request $request)
    {
        $pow = app('pow');
        $productId = (int) $request->input('product_id');
        $product = $pow->product()->findById($productId);

        if(empty($product)) {
            return back()->withErrors(['message' => 'Invalid Product']);
        }

        $qty = (int) $request->input('qty', 1);
        $pow->basket()->addProduct($product, $qty ?? 1);

        return redirect()->route('basket');
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function removeProductAction(Request $request)
    {
        $pow = app('pow');
        $productId = (int) $request->input('product_id');
        $product = $pow->product()->findById($productId);

        if(empty($product)) {
            return back()->withErrors(['message' => 'Invalid Product']);
        }

        $pow->basket()->removeProduct($product);

        return redirect()->route('basket');
    }

    public function clearAction()
    {
        $pow = app('pow');
        $pow->basket()->clearBasket();

        return redirect()->route('basket');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function checkoutAction()
    {
        $pow = app('pow');
        $pow->checkout();


        return view('pow::basket.stripe-pay', [
            'publishable_key' => \Config::get('pow.stripe_options.publishable_key')
        ]);
    }

    public function payAction()
    {

    }

}
