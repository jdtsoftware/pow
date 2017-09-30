<?php

namespace JDT\Pow\Http\Controllers;

use JDT\Api\Payload;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use JDT\Api\Contracts\ApiEndpoint;
use Illuminate\Routing\Controller as BaseController;

/**
 * Class ProductController
 * @package JDT\Pow\Http\Controllers
 */
class ProductController extends BaseController
{

    /**
     * @param int $page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function listAction($page = 1)
    {
        $pow = app('pow');

        return view(
            'pow::product.list',
            [
                'products' => $pow->shop()->list($page),
            ]
        );
    }

    /**
     * @param $productId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewAction($productId)
    {
        $pow = app('pow');
        $product = $pow->product()->findById($productId);

        return view('pow::product.view', ['product' => $product]);
    }
}
