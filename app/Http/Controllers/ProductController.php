<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *      schema="image",
 *      type="object",
 *      example={"id":1, "url": "https://via.placeholder.com/640x480.png/002255?text=dolorem"}
 * )
 *
 * @OA\Schema(
 *      schema="product",
 *      @OA\Property(
 *            property="id",
 *            type="integer",
 *            example="1"
 *      ),
 *      @OA\Property(
 *          property="name",
 *          type="string",
 *          example="NBA Sideline Pullover Satin Jacket 防風套衫 湖人 紫"
 *      ),
 *      @OA\Property(
 *          property="price",
 *          type="integer",
 *          example="1300"
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="images",
 *          type="array",
 *          @OA\Items(
 *              anyOf={
 *                  @OA\Schema(ref="#/components/schemas/image"),
 *                  @OA\Schema(ref="#/components/schemas/image"),
 *                  @OA\Schema(ref="#/components/schemas/image"),
 *              }
 *          )
 *      ),
 *      @OA\Property(
 *          property="category",
 *          type="object",
 *          example={"id": 5, "name": "上衣"}
 *      ),
 * )
 *
 * @OA\Schema(
 *      schema="products",
 *      type="array",
 *      @OA\Items(
 *              anyOf={
 *                   @OA\Schema(ref="#/components/schemas/product"),
 *                   @OA\Schema(ref="#/components/schemas/product"),
 *                   @OA\Schema(ref="#/components/schemas/product"),
 *              }
 *      )
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/products/{category_name}",
     *   tags={"Products"},
     *   summary="根據分類名稱，查找商品列表",
     *   @OA\Parameter(
     *          name="category_name",
     *          description="分類名稱",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string",
     *              default="上衣"
     *          )
     *      ),
     *   @OA\Response(
     *          response="200",
     *          description="請求成功",
     *          @OA\JsonContent(ref="#/components/schemas/products")
     *          ),
     *   @OA\Response(response="422",
     *                description="分類名稱有誤",
     *                @OA\JsonContent(
     *                      example={"message": "category name error"}
     *                )
     *              )
     * )
     */
    public function index(String $category_name)
    {
        if (Category::where('name', $category_name)->count() === 0) {
            return response(['message' => 'category name error'], 422);
        }
        return Product::with(['images:id,url,product_id', 'category:id,name'])->whereHas('category', function ($q) use ($category_name) {
            $q->where('name', $category_name);
        })->get();
    }

    /**
     * @OA\Get(
     *   path="/api/products/{category_name}/{product_id}",
     *   tags={"Products"},
     *   summary="根據分類名稱和商品id，查找指定商品",
     *   @OA\Parameter(
     *          name="category_name",
     *          description="分類名稱",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string",
     *              default="上衣"
     *          )
     *   ),
     *   @OA\Parameter(
     *          name="product_id",
     *          description="商品id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              default=2
     *          )
     *   ),
     *   @OA\Response(
     *          response="200",
     *          description="請求成功",
     *          @OA\JsonContent(ref="#/components/schemas/product")
     *          ),
     *   @OA\Response(response="422",
     *                description="分類名稱有誤",
     *                @OA\JsonContent(
     *                      example={"message": "category name error"}
     *                )
     *              )
     * )
     */
    public function show(String $category_name, Product $product)
    {
        if ($product->category->name !== $category_name) {
            return response(['message' => 'category name error'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return $product->fresh(['images', 'category']);
    }
}
