<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }

    public function store(Request $request)
    {
        return Product::create($request->all());
    }

    public function show(Product $product)
    {
        $product['category'] = $product->category;
        return [
            'product' => $product,
            'reviews_count' => $product->reviews()->where('is_confirmed', 1)->get()->count(),
            'reviews_average_rating' => round($product->reviews()->where('is_confirmed', 1)->get()->avg('rating'), 1),
        ];
    }

    public function update(Request $request, Product $product)
    {
        $product->update($request->all());
        return $product;
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->noContent();
    }

    public function getNews(Request $request) {
      //  sleep(10);
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $categoryId = (int) $request->get('categoryId', 0);
        $productsCount = DB::table('products')->count();
        $products = DB::table('products')
            ->orderBy('id', 'desc')
            ->where('category_id', $categoryId)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();
//        foreach ($products as $product) {
//            print $product->category;
//        }
       // Product::where('id', 3);
        $prodRequest = Product::query()
            //->where('category_id', $categoryId)
            ->orderBy('id', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit);
            //->get();

        if ($categoryId > 0) {
            $prodRequest->where('category_id', $categoryId);
        }
        $prod = $prodRequest->get();

        $productList = [];
        $category = '';
        foreach ($prod as $prodItem) {
           $prodItem['category'] = $prodItem->category;
           if ($categoryId > 0) {
               $category = $prodItem->category;
           }
            $productList[] = $prodItem;
        }
//        print_r($prod);
//        print_r($products);
        return [
            'products' => $productList,
            'count' => $productsCount,
            'category' => $category,
        ];
    }
}
