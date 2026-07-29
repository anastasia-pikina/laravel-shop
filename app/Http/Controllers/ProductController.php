<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $categoryCode = $request->get('categoryCode', '');

        $prodRequest = Product::with('category.parent')
            ->orderBy('id', 'desc');

        $category = null;
        if ($categoryCode) {
            $category = ProductCategory::where('code', $categoryCode)->first();
            if ($category) {
                $ids = [$category->id];
                $queue = [$category->id];
                while (!empty($queue)) {
                    $current = array_shift($queue);
                    $children = ProductCategory::where('parent_category_id', $current)->pluck('id')->toArray();
                    $ids = array_merge($ids, $children);
                    $queue = array_merge($queue, $children);
                }
                $prodRequest->whereIn('category_id', $ids);
            }
        }

        $total = (clone $prodRequest)->count();
        $prod = $prodRequest
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $categoryPath = [];
        if ($category) {
            $path = [];
            $cat = $category;
            while ($cat) {
                $path[] = ['name' => $cat->name, 'code' => $cat->code];
                $cat = $cat->parent;
            }
            $categoryPath = array_reverse($path);
        }

        $productList = [];
        foreach ($prod as $prodItem) {
            $prodItem['category'] = $prodItem->category;
            if ($prodItem->image) {
                $prodItem->image = Storage::disk('local')->url('product/source/' . $prodItem->image);
            }
            $productList[] = $prodItem;
        }

        return [
            'products' => $productList,
            'count' => $total,
            'category' => $category,
            'category_path' => $categoryPath,
        ];
    }

    public function show(Product $product)
    {
        $product->load('category.parent');
        $product['category'] = $product->category;

        if ($product->image) {
            $product->image = Storage::disk('local')->url('product/source/' . $product->image);
        }

        $recommended = Product::with('category.parent')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(3)
            ->get();

        foreach ($recommended as $rec) {
            if ($rec->image) {
                $rec->image = Storage::disk('local')->url('product/source/' . $rec->image);
            }
        }

        return [
            'product' => $product,
            'recommended' => $recommended,
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
}
