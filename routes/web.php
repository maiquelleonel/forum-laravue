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


Route::get('/locale/{lang}', function ($lang) {
    session(['lang' => $lang]);
    if ($lang == 'pt-br') {
        putenv("DATE_FORMAT=d/m/Y H:i:s");
    }
    return back();
})->name('locale');

Route::get('/', [
    'as'   => 'app.index',
    'uses' => 'ThreadsController@home',
]);

Route::get('/threads/{id}', [
    'as' => 'thread.show',
    'uses' => 'ThreadsController@show'
]);

Route::get('/threads', [
    'as' => 'threads.index',
    'uses' => 'ThreadsController@index',
]);

Route::get('/threads/{id}/replies', [
    'as'   => 'replies.index',
    'uses' => 'RepliesController@show',
]);

Route::get('/login/{provider}', [
    'as'   => 'social.login',
    'uses' => 'SocialAuthController@redirect'
]);
Route::get('/login/{provider}/callback', 'SocialAuthController@callback');

Route::middleware(['auth'])->group(function () {

    Route::get('/threads/{thread}/edit', [
        'as' => 'thread.edit',
        'uses' => 'ThreadsController@edit',
    ]);

    Route::post('/threads', [
        'as' => 'thread.store',
        'uses' => 'ThreadsController@store',
    ]);

    Route::get('/threads/{thread}/pinner', [
        'as' => 'thread.pinner',
        'uses' => 'ThreadsController@pinner',
    ]);

    Route::put('/threads/{thread}', [
        'as' => 'thread.update',
        'uses' => 'ThreadsController@update',
    ]);

    Route::post('/threads/{thread}/replies', [
        'as' => 'reply.store',
        'uses' => 'RepliesController@store'
    ]);

    Route::get('/replies/{reply}/highlighter', [
        'as' => 'replies.hightlighter',
        'uses' => 'RepliesController@highlighter',
    ]);
});

Auth::routes();
//Route::get('/home', 'HomeController@index')->name('home');
