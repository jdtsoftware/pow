<?php

namespace JDT\Pow\Http\Controllers\Manage;

use Illuminate\Routing\Controller as BaseController;

/**
 * Class WalletsController
 * @package JDT\Pow\Http\Controllers
 */
class WalletsController extends BaseController
{
    public function indexAction()
    {
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
