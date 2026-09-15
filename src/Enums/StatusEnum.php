<?php

namespace App\Enums;

enum StatusEnum: string
{
    case DRAFT = 'draft';
    case PENDINGPAYMENT = 'pending_payment';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    
    public function string()
    {
        $result = "";

        return $result;
    }
}