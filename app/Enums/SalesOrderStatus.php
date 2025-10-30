<?php

namespace App\Enums;

enum SalesOrderStatus: string
{
    case DRAFT = 'draft';
    case APPROVED = 'approved';
    case PARTIALLY_FULFILLED = 'partially_fulfilled';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
