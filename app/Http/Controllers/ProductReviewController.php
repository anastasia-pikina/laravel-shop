<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductReview;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductReviewController extends Controller
{

    public function index(Request $request): array
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $productsCount = DB::table('product_review')
            ->where('product_id', $request->get('product_id'))
            ->where('is_confirmed', true)
            ->count();
        $prod = ProductReview::query()
            ->where('product_id', $request->get('product_id'))
            ->where('is_confirmed', true)
            ->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $limit)->take($limit)->get();
        $productList = [];
        foreach ($prod as $prodItem) {
            $prodItem['user'] = $prodItem->user;
            $productList[] = $prodItem;
        }

        return [
            'reviews' => $productList,
            'count' => $productsCount,
        ];
    }

    public function update(Request $request)
    {
        $fieldList = $request->all();
        $fieldList['user_id'] = Auth::id();

        return ProductReview::create($fieldList);
    }
}
