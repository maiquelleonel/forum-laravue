<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [
    'as' => 'threads.index',
    'uses' => 'ThreadsController@index',
]);

Route::get('/threads/{id}', [
    'as' => 'threads.show',
    'uses' => 'ThreadsController@show'
]);

Route::get('/locale/{lang}', function ($lang) {
    session(['lang' => $lang]);
    return back();
});
