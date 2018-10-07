<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/*
|--------------------------------------------------------------------------
| Transaction Routes
|--------------------------------------------------------------------------
| Routes for initiating M-Pesa transactions
|
*/


use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Lipa Na Mpesa
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'lipanampesa'], function () {
    Route::post('paybill', 'LipaNaMpesa\LipaNaMpesaPaybillController@transactionRequest');
    Route::post('callback', 'LipaNaMpesa\LipaNaMpesaPaybillController@storeResponse');
});


/*
|--------------------------------------------------------------------------
| Business to Customer
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'b2c'], function () {
    Route::post('payment', 'B2C\B2C_Controller@transactionRequest');
    Route::post('callback', 'B2C\B2C_Controller@storeResponse');

});

/*
|--------------------------------------------------------------------------
| Business to Business
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'b2b'], function () {
    Route::post('payment', 'B2B\B2B_Controller@transactionRequest');
    Route::post('callback', 'B2B\B2B_Controller@storeResponse');

});

/*
|--------------------------------------------------------------------------
| Customer to Business
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'c2b'], function () {
    Route::post('register', 'C2B\C2BRegistrationController@registrationRequest');
    Route::post('payment', 'C2B\C2BPaymentController@transactionRequest');
    Route::post('callback', 'C2B\C2BPaymentController@storeResponse');
});

/*
|--------------------------------------------------------------------------
| Authentication Route
|--------------------------------------------------------------------------
| Route for provisioning oauth tokens for using endpoints
|
*/

Route::post('oauth/token', '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken');