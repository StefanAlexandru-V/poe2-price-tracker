<?php

if (!function_exists('format_price')) {
    function format_price(float $value, int $decimals = 4): string
    {
        if ($value >= 1) {
            return number_format($value, min($decimals, 2)) . ' div';
        }

        // show in chaos for sub-1-divine items (1 div ≈ 8.4 chaos right now, but this fluctuates)
        // just show the raw divine value with more precision
        return number_format($value, $decimals) . ' div';
    }
}

if (!function_exists('format_change')) {
    function format_change(?float $change): string
    {
        if ($change === null) return '—';
        $sign = $change >= 0 ? '+' : '';
        return $sign . number_format($change, 1) . '%';
    }
}
