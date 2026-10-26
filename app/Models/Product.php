<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(InventoryBalance::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            $product->sku ??= 'SKU-'.Str::ulid();
        });

        static::created(function (Product $product) {
            $product->inventory()->create(['on_hand' => 0, 'reserved' => 0]);
        });
    }
}
