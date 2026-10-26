<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function adjust(Request $request, Product $product, InventoryService $inventory)
    {
        $data = $request->validate(['delta' => ['required', 'integer', 'between:-1000000,1000000', 'not_in:0']]);
        $inventory->adjust($product->id, $data['delta'], $request->user()->id);

        return back()->with('global', 'Остаток обновлен.');
    }
}
