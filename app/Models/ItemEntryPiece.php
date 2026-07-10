<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('item_entries_pieces')]
class ItemEntryPiece extends Model
{
    protected $table = 'item_entries_pieces';

    protected $fillable = [
        'item_entries_id',
        'name',
        'total_pieces',
    ];

    protected function casts(): array
    {
        return [
            'total_pieces' => 'integer',
        ];
    }

    public function itemEntry(): BelongsTo
    {
        return $this->belongsTo(ItemEntry::class, 'item_entries_id');
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(ItemEntrySize::class, 'item_entries_piece_id');
    }

    public function colors(): HasMany
    {
        return $this->hasMany(ItemEntryColor::class, 'item_entries_piece_id');
    }
}
