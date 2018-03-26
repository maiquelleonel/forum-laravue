<?php

namespace App\Http\Responses;


use Illuminate\Http\JsonResponse;

class ApiResponse extends JsonResponse
{
    /**
     * ApiResponse constructor.
     * @param bool $success
     * @param string $text
     * @param mixed $data
     */
    public function __construct($success = true, $text = "", $data = [])
    {
        $response = [
            "success"   => $success,
            "text"      => $text,
            "data"      => $data
        ];

        parent::__construct($response);
    }
}