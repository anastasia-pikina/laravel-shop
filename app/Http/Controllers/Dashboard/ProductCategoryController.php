<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategoryRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $parentId = $request->query('parent_id');

        $perPage = $request->integer('per_page', config('dashboard.per_page'));

        $categories = ProductCategory::query()
            ->where('parent_category_id', $parentId)
            ->latest('id')
            ->paginate($perPage);

        $products = Product::query()
            ->where('category_id', $parentId)
            ->latest('id')
            ->paginate($perPage);

        $breadcrumbs = [];
        $current = $parentId ? ProductCategory::find($parentId) : null;
        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->parent;
        }

        return view('dashboard.categories.index', compact('categories', 'products', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $categories = ProductCategory::query()->select('id', 'name')->get();
        $parentCategoryId = $request->query('parent_category_id');
        return view('dashboard.categories.create', compact('categories', 'parentCategoryId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductCategoryRequest $request)
    {
        $data = $request->validated();
        ProductCategory::create($data);

        $redirect = isset($data['parent_category_id'])
            ? route('categories.index', ['parent_id' => $data['parent_category_id']])
            : route('categories.index');

        return redirect($redirect)->with('global', 'Запись успешно создана.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductCategory $category)
    {
        $categories = ProductCategory::query()->select('id', 'name')->whereNot('id', $category->id)->get();
        return view('dashboard.categories.edit')
            ->with('category', $category)
            ->with('categories', $categories);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductCategoryRequest $request, ProductCategory $category)
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')->with('global', 'Запись успешно обновлена.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('global', 'Запись успешно удалена.');
    }
}
