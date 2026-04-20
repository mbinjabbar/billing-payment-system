<?php

namespace App\Enums;

enum DocumentType: string {
    case INVOICE = 'Invoice';
    case NF2 = 'NF2 Form';
    case CHEQUE = 'Cheque Image';
    case RECEIPT = 'Receipt';

    public const BILLER_TYPES = [self::INVOICE->value, self::NF2->value];
    public const PAYMENT_POSTER_TYPES = [self::INVOICE->value, self::CHEQUE->value, self::RECEIPT->value];
}