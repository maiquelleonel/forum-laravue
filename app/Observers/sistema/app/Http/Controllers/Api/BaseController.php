<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 1/24/18
 * Time: 14:04
 */

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;

/**
 * @SWG\Swagger(
 *     basePath="/api",
 *     produces={"application/json"},
 *     consumes={"application/json"},
 *          @SWG\SecurityScheme(
 *              name="access_token",
 *              in="header",
 *              type="apiKey",
 *              securityDefinition="apiKey",
 *              description="ACCESS TOKEN"
 * 	        ),
 *          @SWG\Info(
 *              title="ContactaMax API",
 *              version="1.0"
 *          )
 * )
 */
class BaseController extends Controller
{
    /**
     * @param bool $success
     * @param string $text
     * @param array $data
     * @return ApiResponse
     */
    public function response($success = false, $text = "", $data = [])
    {
        return new ApiResponse($success, $text, $data);
    }
}