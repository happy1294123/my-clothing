<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Schema(
 *      schema="image",
 *      type="object",
 *      example={"id":1, "url": "https://via.placeholder.com/640x480.png/002255?text=dolorem"}
 * )
 *
 * @OA\Schema(
 *      schema="inventory",
 *      type="object",
 *      example={"id":1, "color": "黑色", "size": "M", "amount": 3}
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
 *          property="intro",
 *          type="string",
 *          example="this is introduction"
 *      ),
 *      @OA\Property(
 *          property="status",
 *          type="integer",
 *          example="上架",
 *          description="刪除、上架、下架"
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
 *      schema="productsWithInventories",
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
 *          property="intro",
 *          type="string",
 *          example="this is introduction"
 *      ),
 *      @OA\Property(
 *          property="status",
 *          type="integer",
 *          example="上架",
 *          description="刪除、上架、下架"
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
 *      @OA\Property(
 *          property="inventories",
 *          type="array",
 *          @OA\Items(
 *              anyOf={
 *                  @OA\Schema(ref="#/components/schemas/inventory"),
 *                  @OA\Schema(ref="#/components/schemas/inventory"),
 *                  @OA\Schema(ref="#/components/schemas/inventory"),
 *              }
 *          )
 *      )
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
 *                   @OA\Schema(ref="#/components/schemas/product"),
 *                   @OA\Schema(ref="#/components/schemas/product"),
 *              }
 *      )
 * )
 *
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/products",
     *   tags={"Products"},
     *   summary="商品列表",
     *   @OA\Parameter(
     *          name="category",
     *          description="分類名稱",
     *          in="query",
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
    public function index(Request $request)
    {
        // with category
        if ($request->has('category')) {
            $enum_category = join(',', array_map(fn ($row) => $row['name'], Category::all('name')->toArray()));
            $validater = Validator::make($request->all(), [
                'category' => "in:$enum_category"
            ]);

            if ($validater->fails()) {
                return response(
                    ['message' => 'category name error'],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            return Product::with(['images', 'category'])
                            ->whereHas('category', function ($q) use ($request) {
                                $q->where('name', $request->category);
                            })->get();
        }

        // without categoy
        return Product::with(['images', 'category'])->get();

    }

    /**
     * @OA\Get(
     *      path="/api/products/{product_id}",
     *      tags={"Products"},
     *      summary="根據商品id，查找指定商品",
     *      @OA\Parameter(
     *             name="product_id",
     *             description="商品id",
     *             required=true,
     *             in="path",
     *             @OA\Schema(
     *                 type="integer",
     *                 default=2
     *             )
     *      ),
     *      @OA\Response(
     *             response="200",
     *             description="請求成功",
     *             @OA\JsonContent(ref="#/components/schemas/productsWithInventories")
     *             ),
     *      @OA\Response(
     *          response="422",
     *          description="商品id有誤",
     *          @OA\JsonContent(example={"message": "product id does not exists"})
     *          ),
     * )
     */
    public function show(Request $request)
    {
        try {
            $product = Product::findOrFail($request->product);
            return $product->fresh(['images', 'category', 'inventories']);
        } catch (ModelNotFoundException $e) {
            return response(
                ['message' => 'product id does not exists'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
    * @OA\Get(
    *   path="/api/products/recommend",
    *   tags={"Products"},
    *   summary="隨機生成5個推薦商品",
    *   @OA\Response(
    *          response="200",
    *          description="請求成功",
    *          @OA\JsonContent(ref="#/components/schemas/products")
    *           )
    *)
    */
    public function recommend()
    {
        return Product::with(['images', 'category'])->inRandomOrder()->limit(5)->get();
    }
}
