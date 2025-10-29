<?php

namespace App\Enums;

enum OrderState: string
{
    case Pending = 'pending';
    case In_progress = 'in_progress';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Unpaid = 'unpaid';
}