<?php

namespace App\Http\Middleware;

use App\Services\BladeRunner;

use Closure;
use Illuminate\Http\Request;

class AntiRobot
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $bladeRunner = new BladeRunner($request);

        $mustValidate = $bladeRunner->mustValidate();

        if ($request->isMethod(Request::METHOD_POST) && env('APP_ENV') != 'local') {
            if ($mustValidate) {
                if (!$bladeRunner->isValidRequest()) {
                    $request->session()->flash("error", "Atenção, ".
                        "Selecione a opção 'Não sou um robô' e tente novamente");
                    $bladeRunner->incrementPostRequests();
                    return back()->withInput();
                } else {
                    $bladeRunner->clearPostRequests();
                    $bladeRunner->clearDatabaseSessionsSameIp();
                    return $next($request);
                }
            }

            $bladeRunner->incrementPostRequests();
        }

        view()->share('show_captcha', $mustValidate);

        return $next($request);
    }
}
