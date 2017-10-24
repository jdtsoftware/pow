<?php

namespace JDT\Pow\Http\Controllers\Manage;

use App\Services\Helpers\FlashHelper;
use Illuminate\Routing\Controller as BaseController;
use JDT\Pow\Entities\Order\OrderStatus;
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
        $search = $request->get('search');

        $pow = app('pow');
        $orders = $pow->order()->listAll($perPage, $status, $search);

        return view(
            'pow::manage.orders.index',
            [
                'orders' => $orders,
                'wallet_owner' => $pow->wallet()->getOwner(),
                'status' => $status,
                'page' => $request->input('page', 1),
                'search' => $search
            ]
        );
    }

    /**
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewAction($uuid)
    {
        $pow = app('pow');
        $order = $pow->order()->findByUuid($uuid);

        return view(
            'pow::manage.orders.view',
            [
                'order' => $order,
                'wallet_owner' => $pow->wallet()->getOwner()
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

        FlashHelper::success('Order '.$orderUuid.' has been approved');

        return redirect()->route('manage.orders', ['status' => $status]);
    }

    /**
     * @param $uuid
     * @param $hash
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadOrderFormFileAction($uuid, $hash)
    {
        $pow = app('pow');
        $order = $pow->order()->findByUuid($uuid);

        $filePath = \Storage::disk(\Config::get('pow.temp_storage'))->path($hash);
        return response()->download($filePath);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refundAction(Request $request)
    {
        $status = $request->input('status');
        $orderUuid = $request->input('uuid');
        $reason = $request->input('reason');
        $items = $request->input('items');

        $pow = app('pow');
        $pow->refundOrder($orderUuid, $items, $reason);

        FlashHelper::success('Order '.$orderUuid.' has been refunded');

        return redirect()->route('manage.orders', ['status' => $status]);
    }


    /**
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function downloadInvoiceAction($uuid)
    {
        $pow = app('pow');
        if($pow->order()->validOrder($uuid)) {
            $order = $pow->order()->findByUuid($uuid);

            $pdf = \PDF::loadView(
                'pow::order.invoice',
                [
                    'order' => $order,
                    'wallet_owner' => $pow->wallet()->getOwner(),
                    'public_local_path' => public_path()
                ]
            );
            return $pdf->download('invoice-'.$order->id.'.pdf');
        } else {
            return redirect()->route('manage.orders');
        }
    }

    /**
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markOrderPaidAction($uuid)
    {
        $pow = app('pow');

        if ($pow->order()->validOrder($uuid)) {
            $order = $pow->order()->findByUuid($uuid);
            $pow->updateOrderStatus($order, 'complete');
        }

        FlashHelper::success('Order '.$uuid.' marked as paid');

        return redirect()->route('manage.orders');
    }

    /**
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markOrderCreditAction($uuid)
    {
        $pow = app('pow');
        if ($pow->order()->validOrder($uuid)) {
            $order = $pow->order()->findByUuid($uuid);
            $pow->updateOrderStatus($order, 'complete', true);
        }

        FlashHelper::success('Order '.$uuid.' marked as complete without payment');

        return redirect()->route('manage.orders');
    }
}
