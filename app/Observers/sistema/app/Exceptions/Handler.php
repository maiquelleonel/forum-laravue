<?php

namespace App\Exceptions;

use App\Http\Controllers\Admin\ErrorsController;
use Artesaos\Defender\Exceptions\ForbiddenException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        ModelNotFoundException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        if ($request->is("api/*")) {
            return app(ErrorsController::class)->apiResponse($e);
        }

        if ($e instanceof ForbiddenException) {
            if (in_array($request->method(), ["POST", "UPDATE", "DELETE"])) {
                if ($request->isXmlHttpRequest()) {
                    return response()->json(["text"=>"Você não possui permissão para executar essa ação"], 401);
                }
                return back()->with("error", "Você não possui permissão para executar essa ação");
            }
            return app(ErrorsController::class)->forbidden($e);
        }

        return parent::render($request, $e);
    }
}