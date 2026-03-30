<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Requests\ProductRequest;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private const STORAGE_PATH = 'product/source/';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::latest()->paginate(10);
        foreach ($products as $product) {
            if ($product->image) {
                $product->image = Storage::disk('local')->url('product/source/' . $product->image);
            }
        }
        return view('dashboard.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ProductCategory::query()->select('id', 'name')->get();
        return view('dashboard.products.create')->with('categories', $categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $image = $request->file('image');

        if ($image) {
            $path = $image->store(self::STORAGE_PATH, 'public');
            $base = basename($path);
        }

        $data = $request->all();
        $data['image'] = $base ?? null;


        //Product::create($request->validated());
        Product::create($data);

        return redirect()->route('products.index')->with('global', 'Запись успешно создана.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = ProductCategory::query()->select('id', 'name')->get();

        $result = view('dashboard.products.edit', compact('product'))
            ->with('categories', $categories);
        if ($product->image) {
            $result->with('image', Storage::disk('local')->url('product/source/' . $product->image));
        }

        return $result;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        $image = $request->file('image');
        $isRemoveImage = $request->get('remove_image');
        $data = $request->all();

        if ($image) {
            $path = $image->store(self::STORAGE_PATH, 'public');
            $data['image'] = basename($path);
        }

        $old = $product->image;
        if ((isset($data['image']) && $old) || $isRemoveImage) {
            Storage::disk('public')->delete(self::STORAGE_PATH . $old);
            $data['image'] = null;
        }

        $product->update($data);

        return redirect()->route('products.index')->with('global', 'Запись успешно обновлена.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('global', 'Запись успешно удалена.');
    }
}
