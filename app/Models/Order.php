<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'placed_at' => 'datetime', 'expires_at' => 'datetime',
            'cancelled_at' => 'datetime', 'completed_at' => 'datetime',
            'subtotal' => 'decimal:2', 'delivery_amount' => 'decimal:2', 'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function delivery(): HasOne { return $this->hasOne(OrderDelivery::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
}
