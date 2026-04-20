<?php

namespace App\Enums;

enum UserRole: string {
    case ADMIN = 'Admin';
    case BILLER = 'Biller';
    case PAYMENT_POSTER = 'Payment Poster';

    public const ALL_ROLES = [self::ADMIN->value, self::BILLER->value, self::PAYMENT_POSTER->value];
    public const ADMIN_BILLER = 'role:Admin,Biller';
    public const ADMIN_PAYEMNT_POSTER = 'role:Admin,Payment Poster';
    public const ONLY_ADMIN   = 'role:Admin';
}