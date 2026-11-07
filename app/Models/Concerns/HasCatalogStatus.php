<?php

namespace App\Models\Concerns;

use App\Support\EnumCatalog;
use BackedEnum;
use InvalidArgumentException;
use LogicException;
use ValueError;

/**
 * Access status values stored in a catalog table.
 */
trait HasCatalogStatus
{
    /**
     * @return class-string<BackedEnum>
     */
    abstract protected static function statusEnumClass(): string;

    /**
     * @return ?BackedEnum
     * @throws LogicException
     */
    public function getStatusAttribute(): ?BackedEnum
    {
        $id = $this->attributes['status_id'] ?? null;

        return $id === null ? null : app(EnumCatalog::class)->caseForId(static::statusEnumClass(), (int) $id);
    }

    /**
     * @param BackedEnum|string $status
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws ValueError
     */
    public function setStatusAttribute(BackedEnum|string $status): void
    {
        $enumClass = static::statusEnumClass();
        $status = is_string($status) ? $enumClass::from($status) : $status;
        if (! $status instanceof $enumClass) {
            throw new InvalidArgumentException("Expected {$enumClass}");
        }

        $this->attributes['status_id'] = app(EnumCatalog::class)->id($status);
    }

    /**
     * @return string
     * @throws LogicException
     */
    public function statusName(): string
    {
        return app(EnumCatalog::class)->name(static::statusEnumClass(), (int) $this->attributes['status_id']);
    }
}
