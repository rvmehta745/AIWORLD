<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller as Controller;
use App\Library\General;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="InvestorMaxx API",
 *     version="1.0.0",
 *     description="API documentation for InvestorMaxx application",
 *     @OA\Contact(
 *         email="support@investormaxx.com"
 *     )
 * )
 * @OA\Server(
 *     url="/api/v1",
 *     description="API Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearer_token",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter JWT Bearer token"
 * )
 */
class BaseController extends Controller
{

}
