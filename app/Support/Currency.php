<?php

namespace App\Support;

class Currency
{
    public const DEFAULT = 'MXN';

    public const LABELS = [
        'MXN' => 'Pesos (MXN)',
        'USD' => 'Dólares (USD)',
    ];

    public static function codes(): array
    {
        return array_keys(self::LABELS);
    }

    public static function format(float|string|null $amount, ?string $code = null): string
    {
        return '$'.number_format((float) $amount, 2).' '.($code ?? self::DEFAULT);
    }
}
