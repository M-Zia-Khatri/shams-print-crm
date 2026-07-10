<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('item_entries_sizes')]
class ItemEntrySize extends Model
{
    protected $table = 'item_entries_sizes';

    protected $fillable = [
        'item_entries_piece_id',
        'size',
        'percentage',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
        ];
    }

    public function piece(): BelongsTo
    {
        return $this->belongsTo(ItemEntryPiece::class, 'item_entries_piece_id');
    }
}
