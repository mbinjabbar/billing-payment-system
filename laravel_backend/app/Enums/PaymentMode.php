<?php

namespace App\Enums;

enum PaymentMode: string {
    case CASH = 'Cash';
    case CHEQUE = 'Cheque';
    case BANK_TRANSFER = 'Bank Transfer';
    case CREDIT_CARD = 'Credit Card';
    case DEBIT_CARD = 'Debit Card';
    case INSURANCE = 'Insurance';
    case ONLINE_PAYMENT = 'Online Payment';
}