<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(title="my-clothing", version="0.0.1")
 * @OA\server(
 *     url = "http://localhost:8888",
 *     description="本地主機"
 * )
 * @OA\server(
 *     url = "http://dev.laravel-sail.site:8080",
 *     description="expose主機"
 * )
 * @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         description="call url /login, paste token value to here.",
 *         scheme="bearer"
 *  )
 *
 * @OA\Tag(
 *     name="Users",
 *     description="使用者"
 * )
 * @OA\Tag(
 *     name="Products",
 *     description="商品"
 * )
 * @OA\Tag(
 *     name="Inventories",
 *     description="存貨"
 * )
 * @OA\Tag(
 *     name="Carts",
 *     description="購物車"
 * )
 *
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
