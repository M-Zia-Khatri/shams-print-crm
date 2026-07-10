<?php

namespace App\Jobs;

use App\Models\ItemEntry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Throwable;

class StoreItemEntryJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  array{lart_number: string, client_business_name: string, description: string, image_url: string, darjan: int|string, total_amount: float}  $entryData
     * @param  array<int, array{name: string, total_pieces: int|string, colors: array<int, array{type: string, rate: float|int|string, type_color_count: int|string}>, sizes: array<int, array{size: string, percentage: float|int|string}>}>  $pieces
     */
    public function __construct(
        public array $entryData,
        public array $pieces,
        public ?int $itemEntryId = null,
    ) {}

    public function handle(): void
    {
        DB::transaction(function (): void {
            $itemEntry = $this->itemEntryId
                ? ItemEntry::query()->findOrFail($this->itemEntryId)
                : new ItemEntry;

            $itemEntry->fill($this->entryData);
            $itemEntry->save();

            if ($this->itemEntryId) {
                $itemEntry->pieces()->delete();
            }

            foreach ($this->pieces as $pieceData) {
                $piece = $itemEntry->pieces()->create([
                    'name' => $pieceData['name'],
                    'total_pieces' => $pieceData['total_pieces'],
                ]);

                $piece->colors()->createMany($pieceData['colors']);
                $piece->sizes()->createMany($pieceData['sizes']);
            }
        });
    }

    public function failed(?Throwable $exception): void
    {
        report($exception);
    }
}
