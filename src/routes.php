<?php

Route::get('/products', 'JDT\Pow\Http\Controllers\ProductController@listAction')
    ->name('products');
Route::get('/products/{productId}', 'JDT\Pow\Http\Controllers\ProductController@viewAction')
    ->name('products-view');


Route::get('/basket', 'JDT\Pow\Http\Controllers\BasketController@indexAction')
    ->name('basket');
Route::post('/basket/add', 'JDT\Pow\Http\Controllers\BasketController@addProductAction')
    ->name('basket-add-product');
Route::post('/basket/remove', 'JDT\Pow\Http\Controllers\BasketController@removeProductAction')
    ->name('basket-remove-product');
Route::post('/basket/clear', 'JDT\Pow\Http\Controllers\BasketController@clearAction')
    ->name('basket-clear');
Route::post('/basket/order-form/{productId}/update', 'JDT\Pow\Http\Controllers\BasketController@updateOrderFormAction')
    ->name('basket-update-order-form');
Route::post('/basket/order-form/{productId}/{inputId}/upload-file', 'JDT\Pow\Http\Controllers\BasketController@receiveFileAction')
    ->name('basket-update-order-form-file');

Route::post('/order/create', 'JDT\Pow\Http\Controllers\OrderController@createAction')
    ->name('order-create');
Route::get('/order/{uuid}', 'JDT\Pow\Http\Controllers\OrderController@checkoutAction')
    ->name('order-checkout');
Route::post('/order/{uuid}/pay', 'JDT\Pow\Http\Controllers\OrderController@payAction')
    ->name('order-pay');
Route::get('/order/{uuid}/view', 'JDT\Pow\Http\Controllers\OrderController@viewAction')
    ->name('order-view');
Route::get('/order/{uuid}/invoice/download', 'JDT\Pow\Http\Controllers\OrderController@downloadInvoiceAction')
    ->name('order-invoice-download');

Route::get('insufficient-balance', 'JDT\Pow\Http\Controllers\OrderController@insufficientBalanceAction')
    ->name('insufficient-balance');

Route::get('/wallet', 'JDT\Pow\Http\Controllers\WalletController@indexAction')
    ->name('wallet');


Route::get('/manage/products', 'JDT\Pow\Http\Controllers\Manage\ProductsController@indexAction')
    ->name('manage.products');

Route::get('/manage/products/edit/{productId}', 'JDT\Pow\Http\Controllers\Manage\ProductsController@editAction')
    ->name('manage.products.edit');
Route::post('/manage/products/edit/{productId}', 'JDT\Pow\Http\Controllers\Manage\ProductsController@updateAction')
    ->name('manage.products.update');

Route::get('/manage/products/create', 'JDT\Pow\Http\Controllers\Manage\ProductsController@createAction')
    ->name('manage.products.create');
Route::post('/manage/products/create/save', 'JDT\Pow\Http\Controllers\Manage\ProductsController@saveAction')
    ->name('manage.products.save');


Route::get('/manage/orders/{status?}', 'JDT\Pow\Http\Controllers\Manage\OrdersController@indexAction')
    ->name('manage.orders');
Route::get('/manage/orders/view/{orderId}', 'JDT\Pow\Http\Controllers\Manage\OrdersController@viewAction')
    ->name('manage.orders.view');
Route::get('/manage/orders/view/{orderId}/invoice/download', 'JDT\Pow\Http\Controllers\Manage\OrdersController@downloadInvoiceAction')
    ->name('manage.orders.download.invoice');
Route::post('/manage/orders/refund', 'JDT\Pow\Http\Controllers\Manage\OrdersController@refundAction')
    ->name('manage.orders.refund');
Route::post('/manage/orders/approve', 'JDT\Pow\Http\Controllers\Manage\OrdersController@approveOrderAction')
    ->name('manage.orders.approve');
Route::get('/manage/orders/{orderId}/{fileHash}', 'JDT\Pow\Http\Controllers\Manage\OrdersController@downloadOrderFormFileAction')
    ->name('manage.orders.download');

Route::post('/manage/orders/{orderId}/paid', 'JDT\Pow\Http\Controllers\Manage\OrdersController@markOrderPaidAction')
    ->name('manage.orders.paid');
Route::post('/manage/orders/{orderId}/credit', 'JDT\Pow\Http\Controllers\Manage\OrdersController@markOrderCreditAction')
    ->name('manage.orders.credit');




Route::get('/manage/wallets', 'JDT\Pow\Http\Controllers\Manage\WalletsController@indexAction')
    ->name('manage.wallets');

Route::post('/wallet/{wallet}/order/create', 'JDT\Pow\Http\Controllers\Manage\WalletsController@createOrderAction')
    ->name('manage.wallet.order.create');