<?php

namespace App\Support;

use App\Enums\DeliveryMethod;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Enums\ReservationStatus;
use BackedEnum;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Resolve backed enum cases through database catalogs.
 */
class EnumCatalog
{
    private const TABLES = [
        DeliveryMethod::class => 'delivery_methods',
        PaymentMethod::class => 'payment_methods',
        OrderStatus::class => 'order_statuses',
        DeliveryStatus::class => 'delivery_statuses',
        PaymentStatus::class => 'payment_statuses',
        RefundStatus::class => 'refund_statuses',
        ReservationStatus::class => 'reservation_statuses',
    ];

    /** @var array<class-string<BackedEnum>, array{by_code: array<string, int>, by_id: array<int, BackedEnum>}> */
    private array $maps = [];

    /**
     * @param BackedEnum $case
     *
     * @return int
     * @throws LogicException
     */
    public function id(BackedEnum $case): int
    {
        return $this->map($case::class)['by_code'][$case->value];
    }

    /**
     * @param class-string<BackedEnum> $enumClass
     * @param int $id
     *
     * @return BackedEnum
     * @throws LogicException
     */
    public function caseForId(string $enumClass, int $id): BackedEnum
    {
        return $this->map($enumClass)['by_id'][$id]
            ?? throw new LogicException("Unknown {$enumClass} ID: {$id}");
    }

    /**
     * @param class-string<BackedEnum> $enumClass
     * @param int $id
     *
     * @return string
     * @throws LogicException
     */
    public function name(string $enumClass, int $id): string
    {
        $this->caseForId($enumClass, $id);

        return DB::table($this->table($enumClass))->where('id', $id)->value('name')
            ?? throw new LogicException("Unknown {$enumClass} ID: {$id}");
    }

    /**
     * @param BackedEnum $case
     *
     * @return string
     * @throws LogicException
     */
    public function nameForCase(BackedEnum $case): string
    {
        return $this->name($case::class, $this->id($case));
    }

    /**
     * @param class-string<BackedEnum> $enumClass
     *
     * @return array
     * @throws LogicException
     */
    private function map(string $enumClass): array
    {
        if (isset($this->maps[$enumClass])) {
            return $this->maps[$enumClass];
        }

        $byCode = [];
        $byId = [];
        foreach (DB::table($this->table($enumClass))->get(['id', 'code']) as $row) {
            $case = $enumClass::tryFrom($row->code);
            if ($case === null) {
                throw new LogicException("Unknown {$enumClass} code in database: {$row->code}");
            }
            $byCode[$row->code] = (int) $row->id;
            $byId[(int) $row->id] = $case;
        }

        if (count($byCode) !== count($enumClass::cases())) {
            throw new LogicException("Catalog {$this->table($enumClass)} does not match {$enumClass}");
        }

        return $this->maps[$enumClass] = ['by_code' => $byCode, 'by_id' => $byId];
    }

    /**
     * @param class-string<BackedEnum> $enumClass
     *
     * @return string
     * @throws LogicException
     */
    private function table(string $enumClass): string
    {
        return self::TABLES[$enumClass] ?? throw new LogicException("Unsupported catalog enum: {$enumClass}");
    }
}
