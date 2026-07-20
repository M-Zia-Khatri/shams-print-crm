<?php

namespace App\Http\Controllers\Api;

use App\Enums\DailyShift;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDailyLaberiEntry;
use App\Models\Expense;
use App\Models\ItemEntry;
use App\Models\ItemPaymentReceived;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SyncController extends Controller
{
    public function itemEntries(Request $request): JsonResponse
    {
        $since = $this->since($request);

        return response()->json([
            'created' => $this->createdQuery(ItemEntry::query(), $since)->get(),
            'updated' => $this->updatedQuery(ItemEntry::query(), $since)->get(),
            // Item entries are hard-deleted today and have no deleted_at column, so deleted IDs cannot be truthfully synced.
            'deleted' => [],
            'server_time' => $this->serverTime(),
        ]);
    }

    public function expenses(Request $request): JsonResponse
    {
        $since = $this->since($request);

        return response()->json([
            'created' => $this->createdQuery(Expense::query(), $since)->get(),
            'updated' => $this->updatedQuery(Expense::query(), $since)->get(),
            'deleted' => $this->deletedIds(Expense::withTrashed(), $since),
            'server_time' => $this->serverTime(),
        ]);
    }

    public function employeeDailyLaberi(Request $request): JsonResponse
    {
        $since = $this->since($request);

        return response()->json([
            'created' => $this->createdQuery(EmployeeDailyLaberiEntry::query()->with('employee:id,name'), $since)->get(),
            'updated' => $this->updatedQuery(EmployeeDailyLaberiEntry::query()->with('employee:id,name'), $since)->get(),
            'deleted' => $this->deletedIds(EmployeeDailyLaberiEntry::withTrashed(), $since),
            'server_time' => $this->serverTime(),
        ]);
    }

    public function itemPaymentReceiveds(Request $request): JsonResponse
    {
        $since = $this->since($request);

        return response()->json([
            'created' => $this->createdQuery(ItemPaymentReceived::query(), $since)->get(),
            'updated' => $this->updatedQuery(ItemPaymentReceived::query(), $since)->get(),
            // Item payment received records are hard-deleted today and have no deleted_at column, so deleted IDs cannot be truthfully synced.
            'deleted' => [],
            'server_time' => $this->serverTime(),
        ]);
    }

    public function dashboardSummary(): JsonResponse
    {
        $today = today();

        return response()->json([
            'total_employees' => Employee::count(),
            'working_today' => EmployeeDailyLaberiEntry::whereDate('laberi_date', $today)
                ->whereIn('daily_shift', [DailyShift::Half->value, DailyShift::Full->value, DailyShift::Darid->value])
                ->count(),
            'leave_today' => EmployeeDailyLaberiEntry::whereDate('laberi_date', $today)
                ->whereIn('daily_shift', [DailyShift::Off->value, DailyShift::Leave->value])
                ->count(),
            'pending_item_payments' => $this->pendingItemPayments(),
            'expense_total' => (float) Expense::sum('total_expense'),
            'server_time' => $this->serverTime(),
        ]);
    }

    private function pendingItemPayments(): float
    {
        $itemEntriesTotal = (float) ItemEntry::sum('total_amount');
        $receivedPaymentsTotal = ItemPaymentReceived::query()
            ->get()
            ->sum(fn (ItemPaymentReceived $payment): float => (float) $payment->received_amount);

        return $itemEntriesTotal - $receivedPaymentsTotal;
    }

    private function since(Request $request): ?Carbon
    {
        $since = trim((string) $request->query('since', ''));

        if ($since === '') {
            return null;
        }

        return Carbon::parse($since);
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function createdQuery(Builder $query, ?Carbon $since): Builder
    {
        if ($since === null) {
            return $query->oldest('id');
        }

        return $query->where('created_at', '>', $since)->oldest('id');
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function updatedQuery(Builder $query, ?Carbon $since): Builder
    {
        if ($since === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where('updated_at', '>', $since)
            ->where('created_at', '<=', $since)
            ->oldest('id');
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return array<int, int>
     */
    private function deletedIds(Builder $query, ?Carbon $since): array
    {
        if ($since === null) {
            return [];
        }

        return $query
            ->whereNotNull('deleted_at')
            ->where('deleted_at', '>', $since)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    private function serverTime(): string
    {
        return now()->toISOString();
    }
}
