<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *      schema="inventoryForChart",
 *      type="object",
 *      example={"id":1, "color": "灰色", "size": "M", "inventory_quantity": 5, "name": "獨家帥氣T恤", "price": 1500, "category": "衣服", "image": "https://via.placeholder.com/640x480.png/002255?text=dolorem"}
 * )
 *
 * @OA\Schema(
 *      schema="inventoriesForChart",
 *      type="array",
 *      @OA\Items(
 *              anyOf={
 *                  @OA\Schema(ref="#/components/schemas/inventoryForChart"),
 *                  @OA\Schema(ref="#/components/schemas/inventoryForChart"),
 *                  @OA\Schema(ref="#/components/schemas/inventoryForChart"),
 *              }
 *          )
 * )
 */
class InventoryController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/inventories",
     *   tags={"Inventories"},
     *   summary="存貨列表",
     *   @OA\Parameter(
     *          name="id",
     *          description="存貨 id",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              default="1,2,3,4,5"
     *          )
     *      ),
     *   @OA\Response(
     *          response="200",
     *          description="請求成功",
     *          @OA\JsonContent(ref="#/components/schemas/inventoriesForChart")
     *          ),
     *   @OA\Response(response="422",
     *                description="需填入存貨id",
     *                @OA\JsonContent(
     *                      example={"message": "inventories id is required"}
     *                )
     *              )
     * )
     */
    public function index(Request $request)
    {
        if (!$request->has('id')) {
            return response()->json(['message' => 'inventories id is required'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $id_ary = explode(',', $request->id);

        return Inventory::whereIn('inventories.id', $id_ary)
                        ->leftJoin('products', 'products.id', '=', 'inventories.product_id')
                        ->leftJoin('product_images', function ($join) {
                            $join->on('product_images.product_id', '=', 'products.id');
                        })
                        ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
                        ->select(
                            'inventories.id',
                            'inventories.color',
                            'inventories.size',
                            'inventories.quantity as inventory_quantity',
                            'products.name',
                            'products.price',
                            'categories.name as category',
                            'product_images.url as image',
                        )
                        ->groupBy('inventories.id')
                        ->get();
    }
}
