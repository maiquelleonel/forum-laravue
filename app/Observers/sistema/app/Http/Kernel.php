<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

use App\Http\Middleware\BlockByManyCreditCards;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\CloudFlareProxies::class,
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // Common Middlewares
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.api'   => \App\Http\Middleware\ApiAuthenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        // Checkout Middlewares
        'customer.canBuy' => \App\Http\Middleware\CustomerCanBuy::class,
        'order.prevent-duplication' => \App\Http\Middleware\OrderInProcess::class,
        'prevent.back' => \App\Http\Middleware\PreventBack::class,
        // Access control using permissions
        'needsPermission' => \Artesaos\Defender\Middlewares\NeedsPermissionMiddleware::class,
        // Simple access control, uses only the groups
        'needsRole' => \Artesaos\Defender\Middlewares\NeedsRoleMiddleware::class,
        // Simple Routes needs permission
        'routeNeedsPermission' => \App\Http\Middleware\RouteNeedsPermission::class,
        //validates presence of click_id on url and store in session
        'validate_click_id' => \App\Http\Middleware\ValidateClickId::class,
        //AntiRobot access
        'anti_robot_access' => \App\Http\Middleware\AntiRobot::class,
        //Insert Google Tag at Views
        'insert_gtm'        => \App\Http\Middleware\InsertGoogleTagCodeAtViews::class,
        //redirect to rt pages
        'redirect_to_rt_pages' => \App\Http\Middleware\RedirectToRTPages::class,
        // Insert Tracking pixel at views
        'insert_tracking_pixel' => \App\Http\Middleware\TrackPageViews::class,
        // Change user locale
        'change_user_locale'    => \App\Http\Middleware\ChangeUserLocale::class,
        //block by try buy with many creditCards
        'block_by_many_credit_cards' => \App\Http\Middleware\BlockByManyCreditCards::class
    ];
}
