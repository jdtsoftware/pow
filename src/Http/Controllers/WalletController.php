<?php

namespace JDT\Pow\Http\Controllers;

use JDT\Api\Payload;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use JDT\Api\Contracts\ApiEndpoint;
use Illuminate\Routing\Controller as BaseController;

/**
 * Class WalletController
 * @package JDT\Pow\Http\Controllers
 */
class WalletController extends BaseController
{
    public function indexAction()
    {
        $pow = app('pow');
        $wallet = $pow->wallet();

        return view('pow::wallet.view', ['wallet' => $wallet]);
    }
}
