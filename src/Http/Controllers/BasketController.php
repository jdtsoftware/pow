<?php

namespace JDT\Pow\Http\Controllers;

use Illuminate\Http\Request;
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

        $orderForms = null;
        $orderFormValidation = null;
        if($pow->basket()->hasOrderForms()) {
            $orderForms = $pow->basket()->getOrderForms();

            $orderFormValidation = [];
            foreach ($orderForms as $basketId => $orderFormInputs) {
                if (empty($orderFormInputs['validation'])) {
                    continue;
                }

                $orderFormValidation[$basketId] = \JsValidator::make($orderFormInputs['validation'], $orderFormInputs['messages']);
            }
        }

        return view(
            'pow::basket.index',
            [
                'basket' => $basket,
                'totals' => $totals,
                'order_forms' => $orderForms,
                'order_form_validators' => $orderFormValidation,
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
        $productId = (int) $request->input('product_id', null);
        $productShopId = (int) $request->input('product_shop_id', null);

        $productShop = $pow->shop()->findById($productShopId);
        $product = $pow->product()->findById($productId);

        if(empty($productShop) && empty($product)) {
            return back()->withErrors(['message' => 'Invalid Product']);
        }

        //$pow->basket()->clearBasket();

        $quantity = (isset($productShop) && $productShop->quantity_lock)
            ? $productShop->quantity :
            $request->input('qty', isset($productShop->quantity) ? $productShop->quantity : 1);

        $pow->basket()->addProduct(
            $productShop->product ? $productShop->product : $product,
            $quantity,
            $productShop
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
        $basketId = (int) $request->input('basket_id');
        $pow->basket()->removeProduct($basketId);
        return redirect()->route('basket');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearAction()
    {
        $pow = app('pow');
        $pow->basket()->clearBasket();

        return redirect()->route('basket');
    }

    /**
     * @param Request $request
     * @param $basketId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrderFormAction(Request $request, $basketId)
    {
        $pow = app('pow');
        $orderForms = $pow->basket()->getOrderForms();

        $validator = \Validator::make($request->toArray(), $orderForms[$basketId]['validation']);
        if($validator->valid()) {
            if($pow->basket()->updateOrderForm($request, $basketId)) {
                return response()->json(['response' => 'OK'], 200);
            }
        }

        return response()->json($validator->getMessageBag(), '422');
    }

    /**
     * @param Request $request
     * @param $basketId
     * @param $inputId
     * @return \Illuminate\Http\JsonResponse
     */
    public function receiveFileAction(Request $request, $basketId, $inputId)
    {
        $files = $request->file();

        $pow = app('pow');
        $orderForms = $pow->basket()->getOrderForms();

        if(isset($orderForms[$basketId]['form'][$inputId])) {
            $input = $orderForms[$basketId]['form'][$inputId];

            if(isset($files[$input['name']])) {
                $file = $files[$input['name']];

                $validation = [$input['name'] => $input['validation']];

                $validator = \Validator::make($request->file(), $validation, $input['messages'])->validate();

                if (empty($validator)) {
                    $storage = \Storage::disk(\Config::get('pow.storage_driver'));


                    $hashed = $storage->putFile('', $file);
                    return response()->json(['id' => $storage->url($hashed)], 200);
                } else {
                    return response()->json($validator, 422);
                }
            }
        }

        return response()->json(['error'], 422);
    }
}
