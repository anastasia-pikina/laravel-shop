<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Requests\ProductRequest;
use Illuminate\Support\Facades\Storage;
use App\Storage\ImageSaver;

class ProductController extends Controller
{
    private const STORAGE_PATH = 'product/source/';
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::latest()->paginate($request->integer('per_page', config('dashboard.per_page')));
        return view('dashboard.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $categories = ProductCategory::query()->select('id', 'name')->get();
        $selectedCategoryId = $request->integer('category_id') ?: null;
        return view('dashboard.products.create', compact('categories', 'selectedCategoryId'));
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
           // (new ImageSaver())->imageSaver($path);
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

            if ($isRemoveImage) {
                $data['image'] = null;
            }
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
