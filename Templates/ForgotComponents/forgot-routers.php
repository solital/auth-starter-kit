<?php

use Solital\Core\Course\Router;

/** Forgot Routers */
Router::get('/forgot', 'Auth\ForgotController@forgot')->name('forgot');
Router::post('/forgot-post', 'Auth\ForgotController@forgotPost')->name('forgot.post');
Router::get('/change/{hash}', 'Auth\ForgotController@change')->name('change');
Router::post('/change-post/{hash}', 'Auth\ForgotController@changePost')->name('change.post');
