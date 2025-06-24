<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\PayPalController;
Route::get('/', function () {
    return "Hello World";
});
// In your web.php or api.php
Route::get('/api/order/confirmation/{order_id}', function ($order_id) {
    // Perform necessary actions like displaying a simple confirmation message
    return view('order.confirmation', ['order_id' => $order_id]);
});