<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('item_entries')]
class ItemEntry extends Model
{
    protected $table = 'item_entries';

    protected $fillable = [
        'lart_number',
        'client_business_name',
        'description',
        'image_url',
        'darjan',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'darjan' => 'integer',
            'total_amount' => 'decimal:2',
        ];
    }

    public function pieces(): HasMany
    {
        return $this->hasMany(ItemEntryPiece::class, 'item_entries_id');
    }
}
