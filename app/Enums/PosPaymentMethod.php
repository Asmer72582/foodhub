<?php
namespace App\Enums;

interface PosPaymentMethod
{
    const CASH = 1;
    const ONLINE_UPI = 2;
    const BANK_TRANSFER = 3;
    const UPI = 4;
    const MOBILE_PAYMENT = 5;
}