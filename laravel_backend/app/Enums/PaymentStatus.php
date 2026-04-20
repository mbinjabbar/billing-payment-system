<?php

namespace App\Enums;

enum PaymentStatus: string {
    case COMPLETED = 'Completed';
    case PENDING = 'Pending';
    case REFUNDED = 'Refunded';
    case FAILED = 'Failed';

    public const ALLOWED_EDIT = [self::PENDING->value, self::FAILED->value];
}