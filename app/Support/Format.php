<?php

namespace App\Support;

class Format
{
    /** 45.0 -> "45", 6.5 -> "6.5", 12450 -> "12,450" */
    public static function number(float|int|string|null $value, int $decimals = 1): string
    {
        $formatted = number_format((float) $value, $decimals);

        return str_contains($formatted, '.')
            ? rtrim(rtrim($formatted, '0'), '.')
            : $formatted;
    }

    /** Always one decimal place: 5 -> "5.0", 15.2 -> "15.2" */
    public static function km(float|int|string|null $value): string
    {
        return number_format((float) $value, 1);
    }
}
