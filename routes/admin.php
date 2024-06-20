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

//Admin route groups 

Route::prefix('admin')->group(function () {


    /* User register and Login routes */

    Route::post('/login', 'API\Auth\AdminAuthController@admin_login');
    Route::post('/add-admin', 'API\Auth\AdminAuthController@add_administrator');

    Route::post('/send-otp', 'API\Auth\AuthController@sendOtpCodeAdmin');

    Route::post('/validate-token', 'API\Auth\AuthController@validateOtpcode');

    Route::post('/recover/password', 'API\Auth\AuthController@admin_recover_password');



    /**Private route group for the admininistrator  */


    Route::middleware(['auth:api', 'admin:api'])->group(function () {






        //get f

        Route::get('/get-agent-applications', 'API\FarmsellAgent@getApplications');


        Route::get('/orders', 'AdminController@getOrders');
        Route::get('/order/{id}', 'AdminController@getOrder');


        //logout and settings routes

        Route::post('password/update', 'API\Auth\AdminAuthController@admin_password_update');

        Route::get('/logout', 'API\Auth\AdminAuthController@admin_logout');

        Route::post('/password/update', 'API\Auth\AuthController@user_password_update');

        Route::post('send-email', 'AdminController@sendNewsLetter');

        Route::get('get-email', 'AdminController@getNewsLetter');


        Route::get('get-contact-clicks', 'AdminController@getContactClicks');


        //Country Routes

        Route::post('/add-country', 'AdminController@add_country');
        Route::post('/edit-country', 'AdminController@edit_country');

        //categroy, subcategory, region and district add routes

        Route::post('/add/category', 'AdminController@add_category');
        Route::post('/add/subcategory', 'AdminController@add_subcategory');
        Route::post('/add/region', 'AdminController@add_region');
        Route::post('/add/district', 'AdminController@add_district');

        Route::post('/save-category-banner', 'AdminController@addCategoryBanner');
        Route::get('/get-category-banner/{id}', 'AdminController@getCategoryBanner');

        Route::get('/delete-category-banner/{id}', 'AdminController@delete_exisiting_category_image');

        //category, subcategory, region and district view routes

        Route::get('/get/category-details/{id}', 'AdminController@getCategoryDetails');
        Route::get('/get/category/list', 'AdminController@category_view');
        Route::get('/get/subcategory/list', 'AdminController@subcategory_view');
        Route::get('/get/subcategory/{id}', 'AdminController@getSubCategories');

        Route::get('/get/user-product-statistics/{id}', 'AdminController@userAndProductStatistics');


        Route::get('/get/region/list', 'AdminController@region_view');
        Route::get('/get/district/list', 'AdminController@district_view');

        Route::get('/get/newsLettersEmails', 'AdminController@ViewNewsLettersEmails');

        //category, subcategory, region and district delete routes

        Route::get('/category/delete/{id}', 'AdminController@category_delete');
        Route::get('/subcategory/delete/{id}', 'AdminController@subcategory_delete');

        Route::get('/region/delete/{id}', 'AdminController@region_delete');
        Route::get('/district/delete/{id}', 'AdminController@district_delete');

        Route::get('/statistics/count', 'AdminController@users_count');

        //getting latest users and adds 



        //category, subcategory, region and district delete routes

        Route::post('/category/edit/{id}', 'AdminController@category_edit');
        Route::post('/subcategory/edit/{id}', 'AdminController@subcategory_edit');

        Route::post('/region/edit/{id}', 'AdminController@region_edit');
        Route::post('/district/edit/{id}', 'AdminController@district_edit');

        Route::post('/get/products-paginate', 'AdminController@getProducts');

        Route::get('/get-product-by-popular', 'AdminController@get_seller_products_by_popular');

        Route::get('/product-view/{id}', 'AdminController@productView');

        Route::post('/change-category', 'AdminController@changeCategory');

        Route::post('/get-product/summary', 'AdminController@getProductSummary');

        Route::post('/product-delete-bulk', 'AdminController@deleteProductInBulk');

        //site settings route 

        Route::post('/save-settings', 'AdminController@save_settings');
        Route::get('/get-settings', 'AdminController@get_settings');

        //site pages



        Route::post('/save-page', 'AdminController@addPage');
        Route::get('/get-page', 'AdminController@getPage');

        Route::get('/delete-page/{id}', 'AdminController@deletePage');

        Route::get('/delete-page-content/{id}', 'AdminController@deletePageContent');

        Route::post('/save-page-content', 'AdminController@addPageContent');
        Route::get('/get-page-content/{id}', 'AdminController@getPageContent');

        Route::post('/get-users', 'AdminController@getUsers');
        Route::post('/search-users', 'AdminController@searchUser');
        Route::post('/search-products', 'AdminController@searchProduct');

        Route::get('/get-user/{id}', 'AdminController@getUser');
        Route::get('/get-user-product/{id}', 'AdminController@getUserProduct');
        Route::get('/verify-user/{id}', 'AdminController@verifyUser');

        Route::get('/delete-user/{id}', 'AdminController@deleteUser');
        Route::get('/delete-product/{id}', 'AdminController@deleteProduct');

        Route::get('/approve-product/{id}', 'AdminController@approveProduct');
        Route::get('/reject-product/{id}', 'AdminController@rejectProduct');

        Route::post('/get-pending-product', 'AdminController@getPendingProduct');
        Route::get('/get-admins', 'AdminController@getAdmins');

        Route::get('/export-user', 'AdminController@details');

        Route::post('/add-main-banner', 'AdminController@AddMainBanner');


        Route::get('/get-main-banner', 'AdminController@GetMainBanner');
        Route::get('/delete-main-banner/{id}', 'AdminController@deleteMainBanner');
    });
});



