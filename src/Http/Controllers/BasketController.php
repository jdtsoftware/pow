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
        $totals = $pow->basket()->getTotalPrices();
        return view(
            'pow::basket.index',
            [
                'basket' => $basket,
                'totals' => $totals,
                'incomplete_orders' => $pow->order()->unfinishedOrders()
            ]
        );
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function addProductAction(Request $request)
    {
        $pow = app('pow');
        $productShopId = (int) $request->input('product_shop_id');
        $productShop = $pow->shop()->findById($productShopId);
        
        if(empty($productShop)) {
            return back()->withErrors(['message' => 'Invalid Product']);
        }

        $pow->basket()->addProduct(
            $productShop->product,
            $productShop->quantity,
            $productShop->quantity_lock
        );

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

}
