<?php

use App\Thread;

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

    Route::get('/threads/{thread}/edit', [
        'as' => 'thread.edit',
        'uses' => 'ThreadsController@edit',
    ]);

    Route::post('/threads', [
        'as' => 'thread.store',
        'uses' => 'ThreadsController@store',
    ]);

    Route::put('/threads/{thread}', [
        'as' => 'thread.update',
        'uses' => 'ThreadsController@update',
    ]);
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
