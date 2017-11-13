<?php

namespace JDT\Pow\Http\Controllers\Manage;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use JDT\Pow\Entities\Wallet\Wallet;
use JDT\Pow\Mail\OrderCreated;

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
                'products' => $pow->product()->listAll(),
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

        $quantity = (int) $request->input('quantity', 1);
        $productId = (int) $request->input('product_id');
        $product = $pow->product()->findById($productId);

        if(empty($product)) {
            return back()->withErrors(['message' => 'Invalid Product']);
        }

        $pow->basket('admin')->clearBasket();

        $pow->basket('admin')->addProduct($product, $quantity);

        $order = $pow->createOrderFromBasket('admin');

        if($order) {
            \Mail::to($walletOwner->getContactEmail())
                ->send(new OrderCreated($order));
        }

        return redirect()->route('manage.orders.view', ['uuid' => $order->getUuid()]);
    }

}
