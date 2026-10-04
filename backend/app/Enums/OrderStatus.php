<?php

namespace App\Enums;

enum OrderStatus: string
{
    case RequestReceived = 'request_received';
    case AvailabilityCheck = 'availability_check';
    case OrderedFromSupplier = 'ordered_from_supplier';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::RequestReceived => 'Request received',
            self::AvailabilityCheck => 'Availability check',
            self::OrderedFromSupplier => 'Ordered from supplier',
            self::Paid => 'Paid',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }
}
