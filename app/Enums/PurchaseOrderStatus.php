<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case PARTIALLY_RECEIVED = 'partially_received';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
