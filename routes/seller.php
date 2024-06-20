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

Route::prefix('seller')->group(function () {

    Route::post('/register', 'API\Auth\SellerAuthController@registerSeller');
    Route::post('/login', 'API\Auth\SellerAuthController@loginSeller');
    Route::post('/resend-code', 'API\Auth\SellerAuthController@resendOtpToken');
    Route::post('/verify-code', 'API\Auth\SellerAuthController@validateOtpcode');

    /* 
    for testing api using phpunit
    Route::group(['prefix'=>'test'],function () {
     */
    
    Route::middleware('auth:api')->group(function () {

        Route::get('/get-product', 'API\SellerController@get_seller_products');
        Route::get('/get-dashboard-stats', 'API\SellerController@get_seller_dashboard_Statistics');
        Route::get('/get-product-all', 'API\SellerController@get_seller_prouducts');
        Route::get('/get-product-details/{id}', 'API\SellerController@get_product_details');

        Route::get('/delete-product/{id}', 'API\SellerController@delete_product');

        Route::post('/hide-show-product', 'API\SellerController@toggle_product_availability');
 
        Route::get('/get-product-by-popular', 'API\SellerController@get_seller_products_by_popular');

        /* Message routes */

        Route::get('chat', "API\MessageController@index");
        Route::get('chat/show/{id}', "API\MessageController@show");
        Route::post('chat/save', "API\MessageController@chat");
        Route::get('/chat/delete/{chat}', "API\MessageController@destroy");

        /* Product request  chat routes */
        Route::post('product-request/chat/show', "API\ProductRequestChat@show");
        Route::post('product-request/chat/save', "API\ProductRequestChat@chat");
        Route::get('product-request/chat/delete/{chat}', "API\ProductRequestChat@destroy");


        /* Product Inquiry  chat routes */

        Route::post('product-inquiry/reply', "API\ProductInquiryController@save_inquiry_reply");
        Route::get('product-inquiry/lists', "API\ProductInquiryController@get_inquiry_lists_seller");

        Route::post('/add/advert-mobile', 'UserController@add_advert_mobile');
        Route::post('/edit/advert', 'UserController@edit_advert');
        Route::get('/get-product-for-edit/{id}', 'UserController@get_product_for_edit');

        /* get message, inquiries and product request  counts*/
        Route::get('/profile-notification-count', 'API\SellerController@profile_notification_count');
        
    });
});
