<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceOrderRequest;
use App\Models\PickupPoint;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function store(PlaceOrderRequest $request, OrderService $orders): JsonResponse
    {
        $order = $orders->place($request->validated(), $request->user()?->id);

        return response()->json([
            'number' => $order->number,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'currency' => $order->currency,
            'subtotal' => $order->subtotal,
            'delivery_amount' => $order->delivery_amount,
            'total' => $order->total,
            'expires_at' => $order->expires_at,
        ], 201);
    }

    public function pickupPoints(): JsonResponse
    {
        return response()->json(PickupPoint::query()->where('is_active', true)->orderBy('name')
            ->get(['id', 'name', 'address']));
    }
}
