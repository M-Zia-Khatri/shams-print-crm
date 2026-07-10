<?php

namespace App\Services;

class ItemEntryCalculationService
{
    /**
     * TODO: Confirm the production business formula for total_amount.
     *
     * Placeholder formula: for each piece, multiply total pieces by each color
     * row's rate and color count, then sum all rows.
     *
     * @param  array<int, array{name: string, total_pieces: int|string, colors: array<int, array{type: string, rate: float|int|string, type_color_count: int|string}>, sizes: array<int, array{size: string, percentage: float|int|string}>}>  $pieces
     */
    public function calculate(array $pieces): float
    {
        $totalAmount = 0.0;

        foreach ($pieces as $piece) {
            foreach ($piece['colors'] as $color) {
                $totalAmount += (int) $piece['total_pieces'] * (float) $color['rate'] * (int) $color['type_color_count'];
            }
        }

        return round($totalAmount, 2);
    }
}
