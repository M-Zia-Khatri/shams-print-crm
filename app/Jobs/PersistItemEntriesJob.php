<?php

namespace App\Jobs;

use App\Models\ItemEntry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class PersistItemEntriesJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, array<string, mixed>>  $entries
     */
    public function __construct(public array $entries)
    {
        //
    }

    public function handle(): void
    {
        $timestamp = Carbon::now();

        $entries = collect($this->entries)->map(function (array $entry) use ($timestamp): array {
            return array_merge($entry, [
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        })->all();

        ItemEntry::query()->insert($entries);
    }
}
