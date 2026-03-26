<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', 'UserController@login');

Route::middleware('auth:api_users')->group(function () {
    Route::get('/site/pages', 'SitePageController@index');
    Route::post('/site/pages', 'SitePageController@store');
    Route::get('/site/pages/{page}', 'SitePageController@show');
    Route::put('/site/pages/{page}/editor', 'SitePageController@updateEditor');
    Route::get('/site/pages/{page}/versions', 'SitePageController@versions');
    Route::get('/site/pages/{page}/versions/{version}', 'SitePageController@showVersion');
    Route::get('/site/pages/{page}/changes', 'SitePageController@changes');
    Route::post('/site/pages/{page}/versions/{version}/restore', 'SitePageController@restoreVersion');
    Route::delete('/site/pages/{page}', 'SitePageController@destroy');
});
