<?php

namespace App\Services\Notifications\Contracts;

use Exception;
use Illuminate\Http\Request;

interface NotificationContract
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function notify(Request $request);

    /**
     * @param Exception $e
     * @return mixed
     */
    public function log(Exception $e);
}