<?php

namespace JDT\Pow\Http\Controllers\Manage;

use Illuminate\Routing\Controller as BaseController;
use JDT\Pow\Http\Requests\SaveProductRequest;

/**
 * Class ProductsController
 * @package JDT\Pow\Http\Controllers
 */
class ProductsController extends BaseController
{
    protected $pow;
    protected $productService;

    /**
     * @param int $page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function indexAction($page = 1)
    {
        $this->setup();
        return view(
            'pow::manage.products.index',
            ['products' => $this->productService->list($page)]
        );
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createAction()
    {
        $this->setup();

        return view(
            'pow::manage.products.create',
            [
                'token_types' => $this->pow->wallet()->tokenTypes(),
                'currency' => \Config::get('pow.currency_sign'),
            ]
        );
    }

    /**
     * @param SaveProductRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveAction(SaveProductRequest $request)
    {
        $this->setup();

        $this->pow->product()->updateOrCreate($request);
        return redirect()->route('manage.products');
    }

    /**
     * @param $productId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function editAction($productId)
    {
        $this->setup();

        $product = $this->productService->findById($productId);
        if(empty($product)) {
            return redirect()->route('manage.products')->with(['invalidProduct' => true]);
        }

        return view(
            'pow::manage.products.edit',
            [
                'product' => $product,
                'token_types' => $this->pow->wallet()->tokenTypes(),
                'currency' => \Config::get('pow.currency_sign'),
            ]
        );
    }

    /**
     * @param $productId
     * @param SaveProductRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAction($productId, SaveProductRequest $request)
    {
        $this->setup();

        $product = $this->productService->findById($productId);
        if(empty($product)) {
            return redirect()->route('manage.products')->with(['invalidProduct' => true]);
        }

        $this->pow->product()->updateOrCreate($request, $product);

        return redirect()->route('manage.products');
    }

    /**
     *
     */
    protected function setup()
    {
        $this->pow = app('pow');
        $this->productService = $this->pow->product();
    }

}
