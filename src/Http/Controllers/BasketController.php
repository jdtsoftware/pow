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
            foreach ($orderForms as $productId => $orderFormInputs) {
                if (empty($orderFormInputs['validation'])) {
                    continue;
                }

                $orderFormValidation[$productId] = \JsValidator::make($orderFormInputs['validation'], $orderFormInputs['messages']);
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
        $productShopId = (int) $request->input('product_shop_id');
        $productShop = $pow->shop()->findById($productShopId);

        if(empty($productShop)) {
            return back()->withErrors(['message' => 'Invalid Product']);
        }

        $pow->basket()->clearBasket();

        $quantity = $productShop->quantity_lock
            ? $productShop->quantity :
            $request->input('qty', $productShop->quantity);

        $pow->basket()->addProduct(
            $productShop,
            $quantity,
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
        $productShopId = (int) $request->input('product_shop_id');
        $productShop = $pow->shop()->findById($productShopId);

        if(empty($productShop)) {
            return back()->withErrors(['message' => 'Invalid Product']);
        }

        $pow->basket()->removeProduct($productShop);

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
     * @param $productShopId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrderFormAction(Request $request, $productShopId)
    {
        $pow = app('pow');
        $orderForms = $pow->basket()->getOrderForms();

        $validator = \Validator::make($request->toArray(), $orderForms[$productShopId]['validation']);
        if($validator->valid()) {
            if($pow->basket()->updateOrderForm($request, $productShopId)) {
                return response()->json(['response' => 'OK'], 200);
            }
        }

        return response()->json($validator->getMessageBag(), '422');
    }

    /**
     * @param Request $request
     * @param $productShopId
     * @param $inputId
     * @return \Illuminate\Http\JsonResponse
     */
    public function receiveFileAction(Request $request, $productShopId, $inputId)
    {
        $files = $request->file();

        $pow = app('pow');
        $orderForms = $pow->basket()->getOrderForms();

        if(isset($orderForms[$productShopId]['form'][$inputId])) {
            $input = $orderForms[$productShopId]['form'][$inputId];

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
