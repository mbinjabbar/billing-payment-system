<?php

namespace App\Enums;

enum BillStatus: string {
    case DRAFT = 'Draft';
    case PENDING = 'Pending';
    case PARTIAL = 'Partial';
    case PAID = 'Paid';
    case CANCELLED = 'Cancelled';
    case WRITTEN_OFF = 'Written Off';

    public const CAN_BE_WRITTEN_OFF = [self::PENDING->value, self::PARTIAL->value];
    public const IS_FINALIZED = [self::CANCELLED->value, self::WRITTEN_OFF->value, self::PAID->value,];
    public const FINANCIAL_ACTIVE = [self::PARTIAL->value, self::WRITTEN_OFF->value, self::PAID->value,];
}