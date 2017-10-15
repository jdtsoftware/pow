<?php

namespace JDT\Pow\Http\Controllers\Manage;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use JDT\Pow\Entities\Wallet\Wallet;

/**
 * Class WalletsController
 * @package JDT\Pow\Http\Controllers
 */
class WalletsController extends BaseController
{
    public function indexAction(Request $request)
    {
        $search = $request->get('search');

        $pow = app('pow');

        return view(
            'pow::manage.wallets.index',
            [
                'wallets' => $pow->listWallets(),
                'wallet_token_types' => $pow->walletTokenTypes(),
                'products' => $pow->shop()->listAll(),
            ]
        );
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function createOrderAction(Request $request, $walletUuid)
    {
        $wallet = Wallet::where('uuid', $walletUuid)->first();
        if(!isset($wallet)) {
            return;
        }

        $walletOwner = $wallet->getOwner();
        if(!isset($walletOwner)) {
            return;
        }

        $pow = app('pow');
        $pow->setWalletOwner($walletOwner);

        $productShopId = (int) $request->input('product_shop_id');
        $productShop = $pow->shop()->findById($productShopId);

        if(empty($productShop)) {
            return back()->withErrors(['message' => 'Invalid Product']);
        }

        $pow->basket('admin')->clearBasket();

        $quantity = $productShop->quantity_lock
            ? $productShop->quantity :
            $request->input('qty', $productShop->quantity);

        $pow->basket('admin')->addProduct(
            $productShop,
            $quantity,
            $productShop->quantity_lock
        );

        $order = $pow->createOrderFromBasket('admin');

        $order->update([
            'order_status_id' => OrderStatus::handleToId('pending'),
        ]);

        return redirect()->route('manage.orders.view', ['uuid' => $order->getUuid()]);
    }

}
