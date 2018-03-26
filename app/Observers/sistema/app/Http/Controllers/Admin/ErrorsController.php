<?php

namespace App\Http\Controllers\Admin;

use App\Http\Responses\ApiResponse;
use Artesaos\Defender\Exceptions\ForbiddenException;
use Illuminate\Http\Exception\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ErrorsController extends Controller
{
    /**
     * @param ForbiddenException $exception
     * @return \Illuminate\Http\Response
     */
    public function forbidden(ForbiddenException $exception)
    {
        return response()->view("admin.errors.403", compact("exception"));
    }

    /**
     * @param \Exception $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiResponse(\Exception $exception)
    {
        $message  = $exception->getMessage();

        if ($exception instanceof NotFoundHttpException) {
            $message = "Not Found";
        }

        if ($exception instanceof HttpResponseException) {
            return $exception->getResponse();
        }

        return new ApiResponse(false, $message);
    }
}