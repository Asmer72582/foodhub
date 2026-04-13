<?php

use App\Enums\PosPaymentMethod;

return [
    PosPaymentMethod::CASH => 'Cash',
    PosPaymentMethod::CARD => 'Card',
    PosPaymentMethod::BANK_TRANSFER => 'Bank Transfer',
    PosPaymentMethod::UPI => 'UPI',
    PosPaymentMethod::MOBILE_PAYMENT => 'Mobile Payment'
];
