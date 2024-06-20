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

Route::prefix('user')->group(function () {


    Route::post('/get-zoho-token', 'API\ZohoController@getToken');
    Route::post('/save-zoho-contact', 'API\ZohoController@getSaveForm');
    Route::post('/verify-zoho-email', 'API\ZohoController@verifyEmail');
    Route::get('test-device-notification', "API\MessageController@test_device");
    /* User Authentication routes */
    //Mobile registration

    Route::post('/register-mobile', 'API\Auth\AuthController@registerMobile');
    Route::post('/register-mobile-v2', 'API\Auth\AuthController@registerMobileV2');
    Route::post('/login-mobile', 'API\Auth\AuthController@loginMobile');
    Route::post('/check-number', 'API\Auth\AuthController@checkNumber');

    Route::post('/send-otp', 'API\Auth\AuthController@sendOtpCode');
    Route::post('/validate-token', 'API\Auth\AuthController@validateOtpcode');
    Route::post('/change-password-recover', 'API\Auth\AuthController@change_password_recover');



    Route::post('/delete-account', 'API\UserProfileApi@delete_user_account');

    Route::post('/register', 'API\Auth\AuthController@register');
    Route::post('/password/recover', 'API\Auth\AuthController@PasswordRecovery');

    Route::post('/login', 'API\Auth\AuthController@login');
    Route::post('/forgot/password', 'API\Auth\ForgotPasswordController@forgot_password');
    Route::post('/password/reset', 'API\Auth\ForgotPasswordController@reset_password');
    Route::get('email/verify/{id}/{hash}', 'API\Auth\VerificationApiController@verify')->name('verificationapi.verify');
    Route::get('/details', 'API\Auth\AuthController@details');

    Route::get('/verifycheck/{id}', 'API\Auth\AuthController@verifyCheck');
    Route::get('/verification-confirm/{id}', 'API\Auth\AuthController@verificationConfirm');

    Route::post('/add/NewsLetterEmail', 'UserController@add_news_letter_email');
    Route::get('/get-product-gallary/{id}', 'UserController@get_product_gallery');

    //log out user when the auth token is not valid
    Route::post('/invalid-token', 'UserController@invalid_token');
    Route::post('/get-settings-user', 'AdminController@get_settings');

    /**  Public routes for the user  */
    Route::post('/contact-us', 'API\SupportController@Contact_us');
    Route::post('/was-this-helpful', 'API\SupportController@was_this_helpful');
    Route::post('/get-was-this-helpful', 'API\SupportController@get_was_this_helpful');


    /**Support center Api routes */




    //Following and unfollowing



    Route::post('/get-profile-count', 'API\UserProfileApi@get_profile_count');
    Route::post('/get-profile-list', 'API\UserProfileApi@get_profile_list');


    Route::post('/get-user-data', 'API\UserProfileApi@get_user_data');

    Route::post('/get/product/{id}', 'UserController@get_user_product');

    Route::post('/get/user-product-list/{id}', 'UserController@get_user_product_list');

    Route::post('/update-product-qty', 'UserController@update_available_quanty');


    Route::post('/close-open-product', 'UserController@close_open_product');
    Route::post('/renew-product', 'UserController@renew_product_date');

    // Route::post('/filter-user/product/{id}', 'CategoryApi@filterUserProduct');


    //Getting adverts/products

    //Getting adverts/products
    // get_the_popular_adverts
    Route::get('/get/Recent_adverts', 'UserController@get_Recent_adverts');
    Route::get('/get/get_the_popular_adverts', 'UserController@get_the_popular_adverts');

    Route::get('/get/home-data', 'UserController@getHomePageData');
    Route::get('/get/top-suppliers', 'UserController@top_suppliers');

    Route::get('/get/hot_deals', 'UserController@get_hot_deals');
    Route::get('/get/adverts/by/category/{id}', 'UserController@get_products_by_category');
    Route::get('/get/adverts/by/sub-category/{id}', 'UserController@get_products_by_sub_category');
    Route::get('/get/adverts/by/region/{id}', 'UserController@get_products_by_region');
    Route::get('/get/adverts/by/district/{id}', 'UserController@get_products_by_district');


    Route::post('/single_product/{id}', 'UserController@single_product');

    Route::post('/related-product/{id}', 'UserController@relatedProductPagination');

    Route::post('/get/product-comments', 'UserController@get_product_comment');
    Route::post('/get/product-comment-replies', 'UserController@get_product_comment_reply');

    //Getting categories, subcategories, region, district

    Route::get('/get/categories', 'UserController@get_categories');
    Route::get('/get/categories-slide', 'UserController@get_categories_slide');
    Route::get('/get/categories/{id}', 'UserController@get_categories_filter');
    Route::get('/get/subcategories', 'UserController@get_subcategories_all');
    Route::get('/get/subcategories/{id}', 'UserController@get_subcategories');
    Route::get('/get/subcategories/upload/{id}', 'UserController@get_subcategories_upload');
    Route::get('/get/regions', 'UserController@get_regions');
    Route::get('/get/districts/{id}', 'UserController@get_districts');
    Route::get('/get/districts', 'UserController@get_districts_all');

    //further category and subcategory route

    Route::get('/get/categories-with-count/{id}', 'API\CategoryApi@get_categories_with_count');

    Route::post('/save-phone-clicks', 'API\StatisticsApi@storeContactRecords');
    // product advert search
    Route::post('/adverts/search', 'UserController@advert_search');
    Route::post('/header/search', 'UserController@search_header');

    Route::post('/advanced/search', 'UserController@advanced_searching');
    // Add report
    Route::post('/add-report', 'UserController@add_report');
    //get pages
    Route::get('/get-pages', 'UserController@get_pages');
    Route::get('/get-page/{id}', 'UserController@get_page_data');



    //Mobile API routes that have abit of difference in implementation

    Route::get('/get/categories-for-mobile/{id}', 'API\CategoryApi@get_category_for_mobile');

    Route::get('/get/categories-pagination/{id}', 'API\CategoryApi@categoryPaginate');


    Route::get('/get/subcategories-for-mobile/{id}', 'API\CategoryApi@get_subcategory_for_mobile');

    Route::get('/subcategories/{id}', 'API\CategoryApi@get_sub_categories');
    Route::get('/subcategories/store/{id}', 'API\CategoryApi@sub_category_store');


    Route::get('/store/details/{id}', 'API\SellerController@getStoreDetails');
    Route::get('/top-suppliers', 'API\SellerController@get_top_suppliers');

    /** Umar's public api end points  */

    Route::get('/get-communities', 'API\GroupChat@getFarmCommunities');
    Route::get('/get-community-questions', 'API\GroupChat@getPopularQuestions');

    //rating public routes 

    Route::get('/get-rating-details-mobile/{id}', 'UserController@product_rating_details');


    Route::post('/check-app-version', 'UserController@checkAppVersion');

    //product chat api's
    Route::post('/save-agent-application', 'API\FarmsellAgent@save');


    Route::post('/mass-category-updates', 'UserController@categoryAndSubCategoryUpdate');


    //end of product chat api's

    /**Private routes for the user  */

    Route::group(['prefix'=>'test'],function () {



        /* Messaging routes */

        Route::get('chat', "API\MessageController@index");
        Route::get('chat/show/{id}', "API\MessageController@show");
        Route::post('chat/save', "API\MessageController@chat");
        Route::get('/chat/delete/{chat}', "API\MessageController@destroy");


        /* Product request  chat routes */

        Route::post('product-request/chat/show', "API\ProductRequestChat@show");
        Route::post('product-request/chat/save', "API\ProductRequestChat@chat");
        Route::get('product-request/chat/delete/{chat}', "API\ProductRequestChat@destroy");


        /* Product Inquiry  chat routes */
        Route::post('product-inquiry/save', "API\ProductInquiryController@save_inquiries");
        Route::post('product-inquiry/reply', "API\ProductInquiryController@save_inquiry_reply");
        Route::get('product-inquiry/lists', "API\ProductInquiryController@get_inquiry_lists_buyer");
        Route::get('product-inquiry/{id}', "API\ProductInquiryController@get_inquiry_details");

        /* --------------------- Product request ----------------------------- */
        Route::post('product-request/save', "API\ProductRequestController@save_product_request");
        Route::get('product-request/details/{id}', "API\ProductRequestController@product_request_details");
        Route::get('product-request/seller/list', "API\ProductRequestController@get_product_request_list_seller");
        Route::get('product-request/buyer/list', "API\ProductRequestController@get_product_request_list_buyer");
        //save user device token changes 

        Route::post('/save-device-token', 'UserController@saveDeviceToken');

        //product chat api's change
        Route::post('/save-product-chat', 'API\ProductChatController@saveMessage');

        Route::post('/product/chat/delete', 'API\ProductChatController@deleteChat');

        Route::post('/get-product-messages', 'API\ProductChatController@getChatMessage');
        Route::post('/chat/messages', 'API\ProductChatController@getChatMessageVersion2');

        Route::get('/get-chat-list', 'API\ProductChatController@getChatList');

        //Product order API endpoints start

        Route::get('/order-display', 'API\OrderController@index');
        Route::post('/order-store', 'API\OrderController@store');
        Route::post('/cancel-or-accept-order', 'API\OrderController@AcceptOrCancelOrder');
        Route::get('/order-show/{id}', 'API\OrderController@show');
        Route::get('/order-delete/{id}', 'API\OrderController@destroy');
        Route::get('/order-view/{id}', 'API\OrderController@show');
        Route::get('/update-order-view/{id}', 'API\OrderController@updateOrderView');
        //hot selling products IsaacOc to Product Order Api
        Route::get('/get-hot-sell-product', 'API\OrderController@getHotSellProduct');

        //Product order API endpoints end



        //check token validity

        Route::post('/token-check', 'API\Auth\AuthController@ValidToken');

        Route::get('/get-user/{id}', 'AdminController@getUser');


        Route::get('/get-message-and-notification-count', 'UserController@getNotificationAndMessageCount');


        //registration completion and logout routes 

        Route::get('/email/resend', 'API\Auth\VerificationApiController@resend')->name('verificationapi.resend');

        Route::post('/password/update', 'API\Auth\AuthController@user_password_update');


        Route::get('/logout', 'API\Auth\AuthController@logout');

        Route::get('/get-follow-records', 'API\UserProfileApi@get_follower_following');
        //profile picture upload 
        Route::post('/update-user-profile', 'API\UserProfileApi@profile_update');

        Route::post('/remove-image-background-profile', 'API\UserProfileApi@removeImage');

        Route::post('/profile-update/{id}', 'UserController@profile_update');

        Route::post('/profilepic-update/{id}', 'UserController@picture_update');

        Route::post('/update-cover-image', 'API\UserProfileApi@cover_picture_update');


        Route::post('/profile-update', 'API\UserProfileApi@picture_update');

        Route::post('/follow-unfollow', 'API\UserProfileApi@follow_user');





        //Product advert add route

        Route::post('/add/advert', 'UserController@add_advert');
        Route::post('/add/advert-mobile', 'UserController@add_advert_mobile');

        //edit advert added 



        Route::post('/edit/advert', 'UserController@edit_advert');



        Route::get('/get-product-for-edit/{id}', 'UserController@get_product_for_edit');

        Route::post('/delete/product/{id}', 'UserController@delete_user_product');

        Route::get('/delete/product-image-first/{id}', 'UserController@deleteProductImageFirst');
        Route::get('/delete/product-image/{id}', 'UserController@deleteProductImage');

        // chat routes all_users

        Route::post('/get/chat_messages', 'UserController@get_chat_messages');
        Route::post('/get/chat_lists', 'UserController@get_chat_lists');


        Route::post('/chat/delete', 'UserController@deleteChat');

        Route::get('/get/get-user-notification', 'UserController@getUserNotifications');

        Route::post('/save/chat_message', 'UserController@chat_message_save');

        Route::post('/update-notification-count', 'UserController@updateNotificationCount');
        Route::get('/delete/notification/{id}', 'UserController@delete_notification');

        Route::get('/mark-notification-as-read/{id}', 'UserController@markNotification');

        Route::post('/get/all_users', 'UserController@get_all_users');

        Route::post('/user/search', 'UserController@user_search');
        Route::post('/messages/new', 'UserController@unread_message');
        Route::post('/unread/notification/count', 'UserController@unread_notification_count');
        Route::post('/unread/messages/count', 'UserController@unread_messages_count');
        Route::post('/unread/messages/count/status-update', 'UserController@unread_messages_status_update');
        Route::post('/unread/messages/read/status-update', 'UserController@read_messages_status_update');
        Route::post('/get/product_chat_user', 'UserController@product_chat_user');


        //Notifications route


        Route::post('/get-notification-data', 'UserController@get_notification_data');

        Route::post('/update-notification', 'UserController@update_notification_status');
        Route::post('/update-notification-mass', 'UserController@update_notification_status_mass');

        //User History

        Route::post('/add/user_history', 'UserController@add_to_User_History');
        Route::post('/get/user_history', 'UserController@get_User_History');
        Route::post('/delete/user_history', 'UserController@delete_user_history');
        Route::post('/delete/user_single_history', 'UserController@delete_user_single_history');


        //online/last seen status

        Route::post('/online-last-seen/status', 'UserController@online_last_seen');


        //comment/product feed

        Route::post('/add/product-comment', 'UserController@product_comment');
        Route::post('/add/product-comment-reply', 'UserController@add_product_comment_reply');

        //product likes and views
        Route::post('/log/product-views', 'UserController@log_product_views');
        Route::post('/log/product-likes', 'UserController@log_product_likes');
        Route::get('/get/product-views', 'UserController@get_product_views');
        Route::get('/get/product-likes', 'UserController@get_product_likes');


        //Wish list 

        Route::post('/add/wishlist', 'UserController@add_to_wishlist');
        Route::post('/get/wishlist', 'UserController@get_wishlist');
        Route::get('/get/wishlist', 'UserController@get_wishlist');
        Route::post('/delete/wishList', 'UserController@delete_wishList_product');


        //product rating route


        Route::post('/product/rate', 'UserController@product_rating');
        Route::post('/product/rates', 'UserController@product_rating');



        Route::post('/save-product-rating', 'UserController@product_rating_mobile');

        Route::post('/save-product-reply', 'UserController@addReviewReply');


        /*---------------------Community help ****umar public --------------------*/

        Route::post('/create-community', 'API\GroupChat@createFarmCommunity');
        Route::post('/join-community', 'API\GroupChat@joinFarmCommunity');
        Route::post('/start-community_question', 'API\GroupChat@startCommunityQuestion');
        Route::post('/reply-community_question', 'API\GroupChat@replyCommunityQuestion');

        Route::get('/get-question-replies/{id}', 'API\GroupChat@getQuestionReplies');


        //check rating status
        Route::get('/check-rating-status/{id}', 'UserController@productRatingStatus');

        Route::get('/profile-notification-count', 'UserController@profile_notification_count');
    });
});
