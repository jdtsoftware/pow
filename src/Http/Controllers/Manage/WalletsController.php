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

        return view('pow::manage.wallets.index');
    }
}
