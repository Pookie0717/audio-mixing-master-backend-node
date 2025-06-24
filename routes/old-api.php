<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

use App\Http\Controllers\Admin\SampleAudioController as AdminSampleAudioController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\GallaryController as AdminGallaryController;
use App\Http\Controllers\Admin\LabelController as AdminLabelController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\SampleAudioController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\ServiceController;
use App\Http\Controllers\Web\ServiceTagController;
use App\Http\Controllers\Web\FavouriteController;
use App\Http\Controllers\Web\GallaryController;
use App\Http\Controllers\Web\GiftController;
use App\Http\Controllers\Web\GiftOrderController;
use App\Http\Controllers\Web\MyGiftController;
use App\Http\Controllers\Web\MeController;
use App\Http\Controllers\Web\leadGenerationController;
use App\Http\Controllers\Web\UploadLeadGenerationController;
use App\Http\Controllers\Web\ContactLeadGenerateController;
use App\Http\Controllers\Web\ServicesPromoCodeController;
use App\Http\Controllers\Web\PayPalController;
use App\Http\Controllers\Web\ChatMessageControllerr;

/*
|--------------------------------------------------------------------------
|                                   AUTH
|--------------------------------------------------------------------------
*/

// API Routes
Route::get('/', function () {
    $routes = Route::getRoutes();
    echo '
        <table style="width: 100%; border-collapse: collapse;" border="1">
            <thead>
                <tr>
                    <th>#</th>
                    <th>URI</th>
                </tr>
            </thead>
            <tbody>
    ';
    $i = 1;
    foreach ($routes as $route) {
        if (str_starts_with($route->uri(), 'api/')) {
            echo "
                <tr>
                    <td>{$i}</td>
                    <td>"
                        . $route->methods()[0] .
                        " - <a href='" . env('APP_URL') . $route->uri() . "'>"
                        . env('APP_URL') . $route->uri()
                        . "</a>
                    </td>
                </tr>
            ";
            $i++; 
        };
    }
    echo '
            </tbody>
        </table>
    ';
    return "";
});


Route::prefix('auth')
    ->name('auth.')
    ->controller(AuthController::class)
    ->group(function () {
        Route::post('/register', 'register')->middleware('guest')->name('register');
        Route::post('/login', 'login')->middleware('guest')->name('login');
        Route::get('/verify-email/{id}/{hash}', 'emailVerify')->name('email-verification');
        Route::post('/forgot-password', 'forgetPassword')->middleware('guest')->name('forget-password');
        Route::post('/reset-password/{email}/{token}', 'resetPassword')->middleware('guest')->name('reset-password');
    });





/*
|--------------------------------------------------------------------------
|                                    WEB
|--------------------------------------------------------------------------
*/

// Sample Audios 
Route::apiResource('/sample-audios', SampleAudioController::class)->only('index', 'show');

// Gallary
Route::apiResource('/gallary', GallaryController::class)->only('index', 'show');

// Category 
Route::apiResource('/categories', CategoryController::class)->only('index', 'show');

// Service Tags
Route::apiResource('/services/tags', ServiceTagController::class)->only('index', 'show');

// Services 
Route::apiResource('/services', ServiceController::class)->only('index', 'show');

// Gifts 
Route::apiResource('/gifts', GiftController::class)->only('index', 'show');

// leadGeneration
Route::apiResource('/lead/generation', leadGenerationController::class)->only('index','show','store','destroy');

// upload lead Generation
Route::apiResource('upload/lead/generation', UploadLeadGenerationController::class)->only('index','show','store','destroy');

// contact lead Generation
Route::apiResource('contact/lead/generation', ContactLeadGenerateController::class)->only('index','show','store','destroy');

// promo-codes insert
Route::apiResource('/promo-codes', ServicesPromoCodeController::class)->only('index','show', 'update', 'destroy');
Route::post('/insert-service-promo-codes', [ServicesPromoCodeController::class, 'insertServicePromoCodes']);
Route::get('/my-promo-codes/verify/{code}', [ServicesPromoCodeController::class, 'verifyPromoCodes']);

//paypal payment route

// // Route for combined payment and subscription
// Route::post('/checkout', [PayPalController::class, 'checkout'])->name('checkout');

// // Route for success callback
// Route::get('/success', [PayPalController::class, 'success'])->name('success');

// // Route for cancel callback
// Route::get('/cancel', [PayPalController::class, 'cancel'])->name('cancel');

Route::post('paypal', [PayPalController::class, 'paypal'])->name('paypal');
Route::post('create-subscription', [PayPalController::class, 'createSubscription'])->name('createSubscription');
Route::get('/fetch/order', [PayPalController::class, 'getOrderDetails']);
Route::get('success', [PayPalController::class, 'success'])->name('success');
Route::get('cancel', [PayPalController::class, 'cancel'])->name('cancel');
// In your web.php or api.php
Route::get('/api/order/confirmation/{order_id}', function ($order_id) {
    // Perform necessary actions like displaying a simple confirmation message
    return view('order.confirmation', ['order_id' => $order_id]);
});
Route::put('status/update/{id}', [PayPalController::class, 'updateStatus']);

//chet post ans get 
Route::post('sent/message',[ChatMessageControllerr::class,'messageSent']);
//fatch massage against chatId
Route::get('massage/fatch',[ChatMessageControllerr::class,'massageFetch']);
//fatch active friend for userId
Route::get('chat/list',[ChatMessageControllerr::class,'chatList']);



Route::middleware('auth:sanctum')
    ->group(function () {

        // Me
        Route::apiResource('/me', MeController::class)->only('index', 'store');

        // My Favourites 
        Route::apiResource('/my-favourites', FavouriteController::class)->only('index', 'store');

        // My Favourites 
        Route::delete('favourites/delete', [FavouriteController::class, 'Favouritedestroy']);

        // Cart
        Route::apiResource('/cart', CartController::class)->only('index', 'store', 'update', 'destroy');

        // My Gifts 
        Route::apiResource('/my-gifts', MyGiftController::class)->only('index', 'store');
        Route::get('/my-gifts/verify/{code}', [MyGiftController::class, 'verify'])->name('my-gifts.verify');

        // Orders 
        Route::apiResource('/orders', OrderController::class)->only('index', 'show', 'store');
    });





/*
|--------------------------------------------------------------------------
|                                   ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth:sanctum')
    ->group(
        function () {
            // Sample Audios 
            Route::apiResource('/sample-audios', AdminSampleAudioController::class)->only('index', 'store', 'show', 'update', 'destroy');
            Route::put('/sample-audios/{id}/status', [AdminSampleAudioController::class, 'updateStatus'])->name('sample-audios.update.status');

            // Galalry 
            Route::apiResource('/gallary', AdminGallaryController::class)->only('index', 'store', 'show', 'update', 'destroy');

            // Users 
            Route::apiResource('/users', AdminUserController::class)->only('index', 'store', 'show', 'update', 'destroy');
            Route::put('/users/{id}/status', [AdminUserController::class, 'updateStatus'])->name('users.update.status');

            // Labels
            Route::apiResource('/labels', AdminLabelController::class)->only('index', 'store', 'show', 'update', 'destroy');
            
            
            // Tags
            Route::apiResource('tags', AdminTagController::class)->only('index', 'store', 'show', 'update', 'destroy');


            // Category
            Route::apiResource('/categories', AdminCategoryController::class)->only('index', 'store', 'show', 'update', 'destroy');
            Route::put('/categories/{id}/status', [AdminCategoryController::class, 'updateStatus'])->name('category.update.status');

            // Service
            Route::apiResource('/services', AdminServiceController::class)->only('index', 'store', 'show', 'update', 'destroy');
            Route::put('/services/{id}/status', [AdminServiceController::class, 'updateStatus'])->name('services.update.status');
        }
    );
