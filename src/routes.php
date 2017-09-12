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
Route::get('/basket/checkout', 'JDT\Pow\Http\Controllers\BasketController@checkoutAction')
    ->name('basket-checkout');
Route::post('/basket/pay', 'JDT\Pow\Http\Controllers\BasketController@payAction')
    ->name('basket-pay');

