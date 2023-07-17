<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(String $category_name)
    {
        return Product::with(['images:id,url,product_id', 'category:id,name'])->whereHas('category', function ($q) use ($category_name) {
            $q->where('name', $category_name);
        })->get();
    }

    public function show(String $category_name, Product $product)
    {
        if ($product->category->name !== $category_name) {
            return response(['message' => 'category error'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return $product;
    }
}
