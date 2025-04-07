<?php

use Solital\Core\Course\Router;

/** Login Routers */

Router::get('/auth', 'Auth\LoginController@auth')->name('auth');
Router::post('/auth-post', 'Auth\LoginController@authPost')->name('auth.post');

Router::group(['middleware' => middleware('auth')], function () {
    Router::get('/dashboard', 'Auth\LoginController@dashboard')->name('dashboard');
    Router::get('/logoff', 'Auth\LoginController@exit')->name('logoff');
});
