<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function adjust(int $productId, int $delta, ?int $actorId = null): InventoryBalance
    {
        return DB::transaction(function () use ($productId, $delta, $actorId) {
            $balance = InventoryBalance::query()->whereKey($productId)->lockForUpdate()->firstOrFail();
            if ($balance->on_hand + $delta < $balance->reserved || $balance->on_hand + $delta < 0) {
                throw ValidationException::withMessages(['quantity' => 'Недостаточно свободного остатка.']);
            }
            $balance->increment('on_hand', $delta);
            InventoryMovement::create([
                'product_id' => $productId,
                'actor_user_id' => $actorId,
                'delta' => $delta,
                'reason' => 'adjustment',
                'idempotency_key' => 'adjustment:'.(string) \Illuminate\Support\Str::uuid(),
            ]);

            return $balance->refresh();
        });
    }

    public function release(StockReservation $reservation): void
    {
        if ($reservation->status !== 'active') {
            return;
        }
        $balance = InventoryBalance::query()->whereKey($reservation->item->product_id)->lockForUpdate()->firstOrFail();
        $balance->decrement('reserved', $reservation->quantity);
        $reservation->update(['status' => 'released', 'released_at' => now()]);
    }

    public function consume(StockReservation $reservation): void
    {
        if ($reservation->status !== 'active') {
            return;
        }
        $item = $reservation->item;
        $balance = InventoryBalance::query()->whereKey($item->product_id)->lockForUpdate()->firstOrFail();
        $balance->decrement('reserved', $reservation->quantity);
        $balance->decrement('on_hand', $reservation->quantity);
        $reservation->update(['status' => 'consumed', 'consumed_at' => now()]);
        InventoryMovement::create([
            'product_id' => $item->product_id,
            'order_item_id' => $item->id,
            'delta' => -$reservation->quantity,
            'reason' => 'sale',
            'idempotency_key' => 'sale:'.$item->id,
        ]);
    }

    public function returnSale(StockReservation $reservation): void
    {
        if ($reservation->status !== 'consumed') {
            return;
        }
        $item = $reservation->item;
        $balance = InventoryBalance::query()->whereKey($item->product_id)->lockForUpdate()->firstOrFail();
        $key = 'return:'.$item->id;
        if (InventoryMovement::query()->where('idempotency_key', $key)->exists()) {
            return;
        }
        $balance->increment('on_hand', $reservation->quantity);
        InventoryMovement::create([
            'product_id' => $item->product_id,
            'order_item_id' => $item->id,
            'delta' => $reservation->quantity,
            'reason' => 'return',
            'idempotency_key' => $key,
        ]);
    }
}
