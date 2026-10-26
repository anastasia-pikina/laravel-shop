<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime', 'amount' => 'decimal:2'];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function refunds(): HasMany { return $this->hasMany(PaymentRefund::class); }
}
