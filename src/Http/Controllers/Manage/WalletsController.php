<?php

namespace JDT\Pow\Http\Controllers\Manage;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

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
                'wallet_token_types' => $pow->walletTokenTypes()
            ]
        );
    }
}
