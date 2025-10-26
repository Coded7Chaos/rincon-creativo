<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Fulfillment = 'fulfillment';
    case Client = 'client';
}