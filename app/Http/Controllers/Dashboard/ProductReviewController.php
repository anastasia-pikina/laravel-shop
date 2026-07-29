<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductReview;
use App\Http\Requests\ProductReviewRequest;

class ProductReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reviews = ProductReview::latest()->paginate(10);
        return view('dashboard.reviews.index', compact('reviews'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.reviews.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductReviewRequest $request)
    {
        ProductReview::create($request->validated());

        return redirect()->route('reviews.index')->with('global', 'Запись успешно создана.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductReview $review)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductReview $review)
    {
        return view('dashboard.reviews.edit', compact('review'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductReviewRequest $request, ProductReview $review)
    {
//        print_r($request->validated());
//        die;
        $review->update($request->validated());

        return redirect()->route('reviews.index')->with('global', 'Запись успешно обновлена.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductReview $review)
    {
        $review->delete();
        return redirect()->route('reviews.index')->with('global', 'Запись успешно удалена.');
    }

    public function confirm(ProductReview $review)
    {
        $review->update(['is_confirmed' => !$review->is_confirmed]);
        return redirect()->route('reviews.index');
    }
}
