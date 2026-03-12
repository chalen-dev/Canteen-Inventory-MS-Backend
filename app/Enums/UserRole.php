<?php

namespace App\Enums;

enum UserRole : string
{
    case ADMIN = 'admin';
    case CASHIER = 'cashier';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
