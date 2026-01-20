<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductReviews;

class ProductReviewsController extends Controller
{
    public function getProductReviews(int $productId): array
    {
       // sleep(10);
        return ProductReviews::where('product_id', $productId)->get()->toArray();
    }

    public function store(Request $request)
    {
        sleep(10);
        $data = $request->all();
        return ProductReviews::create($data);
    }
}
