<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *      schema="validateError",
 *      @OA\Property(
 *          property="message",
 *          type="string",
 *          example="The name field is required."
 *      ),
 *      @OA\Property(
 *          property="errors",
 *          type="object",
 *          example={"name": {"The name field is required."}}
 *      )
 * )
 *
 * @OA\Schema(
 *      schema="userInfo",
 *      @OA\Property(
 *          property="id",
 *          type="integer",
 *          example=1
 *      ),
 *      @OA\Property(
 *          property="name",
 *          type="string",
 *          example="allen"
 *      ),
 *      @OA\Property(
 *          property="email",
 *          type="string",
 *          example="allen@example.com"
 *      ),
 *      @OA\Property(
 *          property="phone",
 *          type="string",
 *          example="0912345678"
 *      ),
 *      @OA\Property(
 *          property="address",
 *          type="string",
 *          example=null
 *      )
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/register",
     *   tags={"Users"},
     *   summary="註冊會員",
     *   @OA\RequestBody(
     *       request="RegisterRequestBody",
     *       description="註冊會員的請求範例",
     *       required=true,
     *       @OA\JsonContent(
     *            @OA\Property(
     *                property="name",
     *                type="string",
     *                example="allen"
     *            ),
     *            @OA\Property(
     *                property="email",
     *                type="string",
     *                example="allen@example.com"
     *            ),
     *            @OA\Property(
     *                property="password",
     *                type="string",
     *                example="12345678"
     *            ),
     *            @OA\Property(
     *                property="password_confirmation",
     *                type="string",
     *                example="12345678"
     *            ),
     *            @OA\Property(
     *                property="phone",
     *                type="string",
     *                example=""
     *            ),
     *            @OA\Property(
     *                property="address",
     *                type="string",
     *                example=""
     *            ),
     *      )
     *   ),
     *   @OA\Response(
     *      response="201",
     *      description="請求成功",
     *      @OA\MediaType(mediaType="application/json")
     *   ),
     *   @OA\Response(
     *      response="409",
     *      description="email已經被使用過",
     *      @OA\JsonContent(example={"message": "email already exist"})
     *   ),
     *   @OA\Response(
     *      response="422",
     *      description="驗證請求有誤",
     *      @OA\JsonContent(ref="#/components/schemas/validateError")
     *   )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email',
            'password' => 'required|confirmed'
        ]);

        $user = User::firstOrCreate([
            'email' => $request->email
        ], [
            'name' => $request->name,
            'password' => $request->password,
            'phone' => $request->phone,
            'address' => $request->address
        ]);

        if ($user->wasRecentlyCreated) {
            return response('', Response::HTTP_CREATED);
        }

        // email already exist
        return response(['message' => 'email already exist'], Response::HTTP_CONFLICT);
    }

    /**
     * @OA\Post(
     *   path="/api/login",
     *   tags={"Users"},
     *   summary="登入",
     *   @OA\RequestBody(
     *       request="LoginRequestBody",
     *       description="登入會員的請求範例",
     *       required=true,
     *       @OA\JsonContent(
     *            @OA\Property(
     *                property="email",
     *                type="string",
     *                example="allen@example.com"
     *            ),
     *            @OA\Property(
     *                property="password",
     *                type="string",
     *                example="12345678"
     *            )
     *      )
     *   ),
     *   @OA\Response(
     *      response="200",
     *      description="請求成功",
     *      @OA\JsonContent(
     *          example={
     *              "user": {
     *                  "id": 1,
     *                  "name": "allen",
     *                  "email": "allen@example.com",
     *                  "phone": "0912345678",
     *                  "address": null
     *              },
     *              "token": "2|UvEntO7H9iN4SF9eJYPNCxVbBN8V0Nv5TnANWUO9"
     *          }
     *      )
     *   ),
     *   @OA\Response(
     *      response="401",
     *      description="email或password有誤",
     *      @OA\JsonContent(example={"message": "Credentials do not match records"})
     *   )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
    
        $user = User::firstWhere('email', $request->email);
    
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'Credentials do not match records'
            ], Response::HTTP_UNAUTHORIZED);
        }
    
        $token = $user->createToken('api-token', ['*'], Carbon::now()->addDays(7))->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *   path="/api/logout",
     *   tags={"Users"},
     *   summary="登出",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *      response="204",
     *      description="請求成功",
     *      @OA\MediaType(mediaType="application/json")
     *   )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Get(
     *   path="/api/user",
     *   tags={"Users"},
     *   summary="用戶資料",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *      response="200",
     *      description="請求成功",
     *      @OA\JsonContent(ref="#/components/schemas/userInfo")
     *
     *   )
     * )
     */
    public function show(Request $request)
    {
        return $request->user();
    }
}
