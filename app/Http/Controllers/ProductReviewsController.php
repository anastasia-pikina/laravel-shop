<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductReviews;
use Illuminate\Support\Facades\DB;

class ProductReviewsController extends Controller
{

    public function index(Request $request): array
    {
     //   sleep(5);
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $productsCount = DB::table('product_reviews')
            ->where('product_id', $request->get('product_id'))
            ->where('is_confirmed', true)
            ->count();
        $prod = ProductReviews::query()
            ->where('product_id', $request->get('product_id'))
            ->where('is_confirmed', true)
            ->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $limit)->take($limit)->get();
        $productList = [];
        foreach ($prod as $prodItem) {
            $prodItem['category'] = $prodItem->category;
            $productList[] = $prodItem;
        }
//        print_r($prod);
//        print_r($products);
        return [
            'reviews' => $productList,
            'count' => $productsCount,
        ];
       // sleep(10);
        //return ProductReviews::where('product_id', $productId)->get()->toArray();
    }

    public function getProductReviewCount($product_id)
    {
        print $product_id  . '111';
        die;

    }

    public function update(Request $request)
    {
        sleep(3);
        return ProductReviews::create($request->all());
    }
}
