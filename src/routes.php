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

Route::post('/order/create', 'JDT\Pow\Http\Controllers\OrderController@createAction')
    ->name('order-create');
Route::get('/order/{uuid}', 'JDT\Pow\Http\Controllers\OrderController@viewAction')
    ->name('order-view');
Route::post('/order/{uuid}/pay', 'JDT\Pow\Http\Controllers\OrderController@payAction')
    ->name('order-pay');
Route::get('/order/{uuid}/complete', 'JDT\Pow\Http\Controllers\OrderController@completeAction')
    ->name('order-complete');
