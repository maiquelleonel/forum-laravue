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

Route::get('/', function () {
    return view('threads.index');
});

Route::get('/threads/{id}', [
    'as' => 'thread.show',
    'uses' => 'ThreadsController@show'
]);

Route::get('/locale/{lang}', function ($lang) {
    session(['lang' => $lang]);
    return back();
});

Route::middleware(['auth'])->group(function () {
    Route::get('/threads', [
        'as' => 'threads.index',
        'uses' => 'ThreadsController@index',
    ]);

    Route::post('/threads', [
        'as' => 'threads.store',
        'uses' => 'ThreadsController@store',
    ]);
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
