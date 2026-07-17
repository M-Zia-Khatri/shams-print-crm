<?php

namespace App\Http\Controllers;

use App\Enums\DailyShift;
use App\Http\Requests\EmployeeShiftRequest;
use App\Models\Employee;
use App\Models\EmployeeDailyLaberiEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeShiftController extends Controller
{
    public function create(): View
    {
        $employees = Employee::active()->orderBy('name')->get();

        return view('employees.shifts.create', [
            'employees' => $employees,
            'shifts' => DailyShift::cases(),
        ]);
    }

    public function store(EmployeeShiftRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $shiftDate = $validated['shift_date'];
        $defaultShift = $validated['default_shift'];
        $checkedEmployeeIds = $validated['employee_ids'];
        $overrides = $validated['overrides'] ?? [];

        $allEmployeeIds = Employee::active()->pluck('id');

        $duplicates = EmployeeDailyLaberiEntry::query()
            ->whereDate('laberi_date', $shiftDate)
            ->whereIn('employee_id', $allEmployeeIds)
            ->exists();

        if ($duplicates) {
            return back()
                ->withInput()
                ->withErrors(['shift_date' => 'One or more employees already have a shift entry for this date.']);
        }

        DB::transaction(function () use ($allEmployeeIds, $checkedEmployeeIds, $overrides, $defaultShift, $shiftDate): void {
            foreach ($allEmployeeIds as $employeeId) {
                $isChecked = in_array($employeeId, $checkedEmployeeIds, true);

                $shift = $isChecked
                    ? ($overrides[$employeeId] ?? $defaultShift)
                    : DailyShift::Leave->value;

                EmployeeDailyLaberiEntry::create([
                    'employee_id' => $employeeId,
                    'laberi_date' => $shiftDate,
                    'daily_shift' => $shift,
                ]);
            }
        });

        return to_route('employees.index')->with('status', 'Shift entries recorded successfully.');
    }
}
