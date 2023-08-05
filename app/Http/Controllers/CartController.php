<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Cart;
use App\Models\Inventory;

/**
 * @OA\Schema(
 *      schema="inventoryReq",
 *      type="object",
 *      example={"inventory_id": 1, "product_quantity": 3}
 * )
 *
 * @OA\Schema(
 *      schema="inventoryReqs",
 *      type="array",
 *      @OA\Items(
 *          anyOf={
 *              @OA\Schema(ref="#/components/schemas/inventoryReq"),
 *              @OA\Schema(ref="#/components/schemas/inventoryReq"),
 *              @OA\Schema(ref="#/components/schemas/inventoryReq"),
 *          }
 *      )
 * )
 *
 * @OA\Schema(
 *      schema="myCartProduct",
 *      type="object",
 *      example={"id":1, "color": "灰色", "size": "M", "product_quantity": 5, "name": "獨家帥氣T恤", "price": 1500, "category": "衣服", "image": "https://via.placeholder.com/640x480.png/002255?text=dolorem"}
 * )
 *
 * @OA\Schema(
 *      schema="myCartProducts",
 *      type="array",
 *      @OA\Items(
 *          anyOf={
 *              @OA\Schema(ref="#/components/schemas/myCartProduct"),
 *              @OA\Schema(ref="#/components/schemas/myCartProduct"),
 *              @OA\Schema(ref="#/components/schemas/myCartProduct"),
 *          }
 *      )
 * )
 *
 */
class CartController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/carts",
     *   tags={"Carts"},
     *   security={{"bearerAuth":{}}},
     *   summary="將存貨加入購物車(覆蓋之前的紀錄)",
     *   @OA\RequestBody(
     *       request="CartsRequestBody",
     *       description="存貨和數量列表",
     *       required=true,
     *       @OA\JsonContent(ref="#/components/schemas/inventoryReqs")
     *   ),
     *   @OA\Response(
     *      response="201",
     *      description="請求成功",
     *   )
     * )
     */
    public function store(Request $request)
    {
        if (count($request->all()) === 0) {
            return response()->json(['message' => 'The request body is required.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $request->validate([
            '*.inventory_id' => 'required',
            '*.product_quantity' => 'required',
        ]);

        Cart::where(['user_id' => Auth::user()->id])->delete();
        foreach($request->all() as $cart) {
            $insert_rows[] = [
                'user_id' => Auth::user()->id,
                'inventory_id' => $cart['inventory_id'],
                'product_quantity' => $cart['product_quantity'],
                'created_at' =>  date('Y-m-d H:i:s'),
                'updated_at' =>  date('Y-m-d H:i:s')
            ];
        }

        Cart::insert($insert_rows);
        return response(null, Response::HTTP_CREATED);
    }

    /**
     * @OA\Delete(
     *   path="/api/carts/{cart_id}",
     *   tags={"Carts"},
     *   security={{"bearerAuth":{}}},
     *   summary="將指定商品從購物車移除",
     *   @OA\Parameter(
     *       name="cart_id",
     *       description="購物車內商品id",
     *       in="path",
     *       @OA\Schema(
     *           type="integer",
     *           default=1
     *       )
     *   ),
     *   @OA\Response(
     *      response="204",
     *      description="請求成功",
     *   )
     * )
     */
    public function delete($cart_id)
    {
        Cart::destroy($cart_id);
        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Delete(
     *   path="/api/carts",
     *   tags={"Carts"},
     *   security={{"bearerAuth":{}}},
     *   summary="清空購物車",
     *   @OA\Response(
     *      response="204",
     *      description="請求成功",
     *   )
     * )
     */
    public function deleteAll()
    {
        Cart::where(['user_id' => Auth::user()->id])->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Post(
     *   path="/api/carts/checkout",
     *   tags={"Carts"},
     *   security={{"bearerAuth":{}}},
     *   summary="清空購物車並扣除存貨",
     *   @OA\Response(
     *      response="204",
     *      description="請求成功",
     *   )
     * )
     */
    public function checkout()
    {
        $carts = Cart::whereUserId(Auth::user()->id);

        foreach($carts->get() as $cart) {
            $inv = Inventory::find($cart->inventory_id);
            $inv->update(['quantity' => $inv->quantity - $cart->product_quantity]);
            $other_carts = Cart::whereInventoryId($cart->inventory_id);
            $other_carts->update(['product_quantity' => $other_carts->product_quantity = $cart->product_quantity]);
        }

        $carts->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Get(
     *   path="/api/carts",
     *   tags={"Carts"},
     *   security={{"bearerAuth":{}}},
     *   summary="個人購物車存貨列表",
     *   @OA\Response(
     *        response="200",
     *        description="請求成功",
     *        @OA\JsonContent(ref="#/components/schemas/myCartProducts")
     *    )
     * )
     */
    public function index()
    {
        $my_cart = Cart::whereUserId(Auth::user()->id);

        $inv_id_ary = array_map(
            fn ($row) => $row['inventory_id'],
            $my_cart->get('inventory_id')->toArray()
        );

        return Inventory::whereIn('inventories.id', $inv_id_ary)
                        ->leftJoin('products', 'products.id', '=', 'inventories.product_id')
                        ->leftJoin('product_images', function ($join) {
                            $join->on('product_images.product_id', '=', 'products.id');
                        })
                        ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
                        ->leftJoin('carts', 'carts.inventory_id', '=', 'inventories.id')
                        ->select(
                            'inventories.id',
                            'inventories.color',
                            'inventories.size',
                            'carts.product_quantity',
                            'products.name',
                            'products.price',
                            'categories.name as category',
                            'product_images.url as image',
                        )
                        ->groupBy('inventories.id')
                        ->get();
    }
}
