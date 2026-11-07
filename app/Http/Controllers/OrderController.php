<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceOrderRequest;
use App\Models\PickupPoint;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

/**
 * Order checkout and pickup point endpoints.
 */
class OrderController extends Controller
{
    /**
     * @param PlaceOrderRequest $request
     * @param OrderService $orders
     *
     * @return JsonResponse
     */
    public function store(PlaceOrderRequest $request, OrderService $orders): JsonResponse
    {
        $order = $orders->place($request->validated(), $request->user()?->id);

        return response()->json([
            'number' => $order->number,
            'status' => $order->status->value,
            'payment_method' => $order->payment_method->value,
            'currency' => $order->currency,
            'subtotal' => $order->subtotal,
            'delivery_amount' => $order->delivery_amount,
            'total' => $order->total,
            'expires_at' => $order->expires_at,
        ], 201);
    }

    /**
     * @return JsonResponse
     */
    public function pickupPoints(): JsonResponse
    {
        return response()->json(PickupPoint::query()->where('is_active', true)->orderBy('name')
            ->get(['id', 'name', 'address']));
    }
}
