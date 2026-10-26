<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PickupPoint;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PickupPointController extends Controller
{
    public function index()
    {
        return view('dashboard.pickup-points.index', ['points' => PickupPoint::query()->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:pickup_points,code'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
        ]);
        PickupPoint::create($data);

        return back()->with('global', 'Точка самовывоза добавлена.');
    }

    public function update(Request $request, PickupPoint $point)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:255', Rule::unique('pickup_points', 'code')->ignore($point->id)],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);
        $point->update($data);

        return back()->with('global', 'Точка самовывоза обновлена.');
    }
}
