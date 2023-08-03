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
 *      example={"inventory_id": 1, "amount": 3}
 * )
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
 */
class CartController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/carts",
     *   tags={"Carts"},
     *   security={{"bearerAuth":{}}},
     *   summary="將存貨加入購物車",
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
            '*.amount' => 'required',
        ]);

        foreach($request->all() as $cart) {
            $insert_rows[] = [
                'user_id' => Auth::user()->id,
                'inventory_id' => $cart['inventory_id'],
                'amount' => $cart['amount'],
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
     * @OA\Put(
     *   path="/api/carts/{cart_id}",
     *   tags={"Carts"},
     *   security={{"bearerAuth":{}}},
     *   summary="更新購物車內指定商品的數量",
     *   @OA\Parameter(
     *       name="cart_id",
     *       description="購物車內商品id",
     *       in="path",
     *       @OA\Schema(
     *           type="integer",
     *           default=1
     *       )
     *   ),
     *   @OA\RequestBody(
     *       request="AmountRequestBody",
     *       description="計算過後的數量",
     *       required=true,
     *       @OA\JsonContent(
     *            example={"amount": 5}
     *       )
     *   ),
     *   @OA\Response(
     *      response="204",
     *      description="請求成功",
     *   )
     * )
     */
    public function update($cart_id, Request $request)
    {
        $cart = Cart::whereId($cart_id);
        $max_amount = $cart->first()->inventory->amount;
        if ($request->amount > $max_amount) {
            return response()->json(['message' => 'The inventory don\'t have enough amount.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        } elseif ($request->amount < 1) {
            return response()->json(['message' => 'The inventory amount is too low.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $cart->update($request->all());
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
            $inv->update(['amount' => $inv->amount - $cart->amount]);
            $other_carts = Cart::whereInventoryId($cart->inventory_id);
            $other_carts->update(['amount' => $other_carts->amount = $cart->amount]);
        }

        $carts->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Post(
     *   path="/api/carts/{inventory_id}",
     *   tags={"Carts"},
     *   security={{"bearerAuth":{}}},
     *   summary="獲取存貨資訊並加入購物車",
     *   @OA\Parameter(
     *       name="inventory_id",
     *       description="存貨id",
     *       in="path",
     *       @OA\Schema(
     *           type="integer",
     *           default=1
     *       )
     *   ),
     *   @OA\Response(
     *      response="200",
     *      description="請求成功",
     *      @OA\JsonContent(ref="#/components/schemas/inventoryForChart")
     *   )
     * )
     */
    public function storeReturnInv($inventory_id)
    {
        Cart::create([
            'user_id' => Auth::user()->id,
            'inventory_id' => $inventory_id,
            'amount' => 1
        ]);

        $inv = Inventory::where('inventories.id', $inventory_id)
                    ->leftJoin('products', 'products.id', '=', 'inventories.product_id')
                    ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
                    ->leftJoin('product_images', 'product_images.product_id', '=', 'products.id')
                    ->select(
                        'inventories.id',
                        'inventories.color',
                        'inventories.size',
                        'inventories.amount',
                        'products.name',
                        'products.price',
                        'categories.name as category',
                        'product_images.url as image',
                    )
                    ->first();
        return response()->json($inv);
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
     *        @OA\JsonContent(ref="#/components/schemas/inventoriesForChart")
     *    )
     * )
     */
    public function index()
    {
        $inv_id_ary = array_map(function ($row) {
            return $row['inventory_id'];
        }, Cart::whereUserId(Auth::user()->id)->get('inventory_id')->toArray());
        $inv_id_str = join(',', $inv_id_ary);

        return app('\App\Http\Controllers\InventoryController')->index(new Request(['id' => $inv_id_str]));
    }
}
