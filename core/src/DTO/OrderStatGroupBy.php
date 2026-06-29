<?php

namespace App\DTO;

enum OrderStatGroupBy: string
{
    case Day   = 'day';
    case Month = 'month';
    case Year  = 'year';

    /** SQL DATE_FORMAT pattern for GROUP BY */
    public function dateFormat(): string
    {
        return match ($this) {
            self::Day   => '%Y-%m-%d',
            self::Month => '%Y-%m',
            self::Year  => '%Y',
        };
    }

    /** Human-readable label for the response */
    public function label(): string
    {
        return match ($this) {
            self::Day   => 'day',
            self::Month => 'month',
            self::Year  => 'year',
        };
    }
}
