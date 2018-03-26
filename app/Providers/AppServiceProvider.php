<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Reply;
use App\User;
use App\Observers\ReplyObserver;
use App\Observers\UserPhotoObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Reply::observe(ReplyObserver::class);
        User::observe(UserPhotoObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
