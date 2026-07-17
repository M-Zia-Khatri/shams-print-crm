<?php

namespace App\Http\Controllers;

use App\Enums\DailyShift;
use App\Models\Employee;
use App\Models\EmployeeDailyLaberiEntry;
use App\Models\PayrollLock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeDailyLaberiController extends Controller
{
    public function index(Request $request, Employee $employee): View
    {
        [$start, $end] = $this->resolveDateRange($request);

        $entries = $employee->dailyLaberiEntries()
            ->betweenDates($start, $end)
            ->latest('laberi_date')
            ->paginate(15)
            ->withQueryString();

        return view('employees.daily-laberi.index', [
            'employee' => $employee,
            'entries' => $entries,
            'filters' => ['start_date' => $start, 'end_date' => $end],
        ]);
    }

    public function create(Employee $employee): View
    {
        return view('employees.daily-laberi.create', [
            'employee' => $employee,
            'shifts' => DailyShift::cases(),
        ]);
    }

    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'laberi_date' => ['required', 'date'],
            'daily_shift' => ['required', Rule::enum(DailyShift::class)],
        ]);

        if (PayrollLock::isDateLocked($validated['laberi_date'])) {
            return back()
                ->withInput()
                ->withErrors(['laberi_date' => 'This date falls inside a locked payroll week.']);
        }

        if ($employee->dailyLaberiEntries()->whereDate('laberi_date', $validated['laberi_date'])->exists()) {
            return back()
                ->withInput()
                ->withErrors(['laberi_date' => 'A shift entry already exists for this employee on this date.']);
        }

        $employee->dailyLaberiEntries()->create($validated);

        return to_route('employees.daily-laberi.index', $employee)->with('status', 'Shift entry recorded.');
    }

    public function destroy(Employee $employee, EmployeeDailyLaberiEntry $entry): RedirectResponse
    {
        $this->ensureEntryBelongsToEmployee($employee, $entry);

        if (PayrollLock::isDateLocked($entry->laberi_date)) {
            return back()->withErrors(['laberi_date' => 'This shift falls inside a locked payroll week.']);
        }

        $entry->delete();

        return to_route('employees.daily-laberi.index', $employee)->with('status', 'Shift deleted.');
    }

    private function ensureEntryBelongsToEmployee(Employee $employee, EmployeeDailyLaberiEntry $entry): void
    {
        abort_unless($entry->employee_id === $employee->id, 404);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveDateRange(Request $request): array
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        if (is_string($start) && $start !== '' && is_string($end) && $end !== '') {
            return [$start, $end];
        }

        return [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()];
    }
}
