<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

//Route::resource('orders', \App\Http\Controllers\OrderController::class)->except(['show']);
Route::get('orders/movements', [OrderController::class, 'getMovements']);
Route::resource('orders', OrderController::class)->except(['destroy']);

Route::post('orders/{order}/complete', [OrderController::class, 'complete']);
Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
Route::post('orders/{order}/resume', [OrderController::class, 'resume']);
