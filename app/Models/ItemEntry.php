<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'lart_number',
    'client_business_name',
    'description',
    'image_url',
    'darjan',
    'total_color',
    'total_rate',
    'total_amount',
    'size_description',
])]
class ItemEntry extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'darjan' => 'integer',
            'total_color' => 'integer',
            'total_rate' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    public static function getDistinctPartyNames()
    {
        return self::query()
            ->select('client_business_name')
            ->distinct()
            ->orderBy('client_business_name')
            ->pluck('client_business_name');
    }
}
