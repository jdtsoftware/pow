<?php

namespace JDT\Pow\Http\Controllers\Manage;

use Illuminate\Routing\Controller as BaseController;

/**
 * Class OrdersController
 * @package JDT\Pow\Http\Controllers
 */
class OrdersController extends BaseController
{
    public function indexAction($page = 1, $perPage = 15)
    {
        $pow = app('pow');
        $orders = $pow->order()->listAll($page, $perPage);

        return view(
            'pow::manage.orders.index',
            [
                'orders' => $orders,
            ]
        );
    }
}
