<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';
}
