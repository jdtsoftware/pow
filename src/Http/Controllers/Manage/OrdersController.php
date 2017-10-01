<?php

namespace JDT\Pow\Http\Controllers\Manage;

use Illuminate\Routing\Controller as BaseController;
use JDT\Pow\Mail\OrderApproved;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OrdersController
 * @package JDT\Pow\Http\Controllers
 */
class OrdersController extends BaseController
{
    public function indexAction(Request $request, $status = null, $perPage = 25)
    {
        $pow = app('pow');
        $orders = $pow->order()->listAll($perPage, $status);


        return view(
            'pow::manage.orders.index',
            [
                'orders' => $orders,
                'status' => $status,
                'page' => $orderUuid = $request->input('page', 1)
            ]
        );
    }

    public function approveOrderAction(Request $request)
    {
        $orderUuid = $request->input('uuid');
        $status = $request->input('status');

        $pow = app('pow');
        $order = $pow->order()->approveOrder($orderUuid);

        if($order) {
            \Mail::to($order->creator->email)
                ->send(new OrderApproved($order));
        }

        return redirect()->route('manage.orders', ['status' => $status]);
    }
}
