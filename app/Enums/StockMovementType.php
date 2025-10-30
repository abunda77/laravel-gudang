<?php

namespace App\Enums;

enum StockMovementType: string
{
    case INBOUND = 'inbound';
    case OUTBOUND = 'outbound';
    case ADJUSTMENT_PLUS = 'adjustment_plus';
    case ADJUSTMENT_MINUS = 'adjustment_minus';
}
