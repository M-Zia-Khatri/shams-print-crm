<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('item_entries_colors')]
class ItemEntryColor extends Model
{
    protected $table = 'item_entries_colors';

    protected $fillable = [
        'item_entries_piece_id',
        'type',
        'rate',
        'type_color_count',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'type_color_count' => 'integer',
        ];
    }

    public function piece(): BelongsTo
    {
        return $this->belongsTo(ItemEntryPiece::class, 'item_entries_piece_id');
    }
}
