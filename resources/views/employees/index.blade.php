<x-app-layout>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <x-section-title title="Employees" subtitle="Manage employees, shifts, and payroll." />

        @if (in_array(auth()->user()?->role, ['super_admin', 'admin'], true))
            <div class="flex gap-2">
                <a href="{{ route('employees.shifts.create') }}">
                    <x-action-button type="submit" variant="secondary">Add Shift</x-action-button>
                </a>
                <a href="{{ route('employees.create') }}">
                    <x-action-button type="submit" variant="primary">Add Employee</x-action-button>
                </a>
            </div>
        @endif
    </div>

    @if (session('status'))
        <div class="alert alert-success shadow-sm">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
        <x-dashboard-card title="Total Employees" description="All employees">
            <div class="text-2xl font-bold text-primary">{{ $widgets['total_employees'] }}</div>
        </x-dashboard-card>
        <x-dashboard-card title="Working Today" description="Active shifts today">
            <div class="text-2xl font-bold text-primary">{{ $widgets['working_today'] }}</div>
        </x-dashboard-card>
        <x-dashboard-card title="Leave Today" description="On leave today">
            <div class="text-2xl font-bold text-primary">{{ $widgets['leave_today'] }}</div>
        </x-dashboard-card>
        <x-dashboard-card title="Weekly Payroll" description="This week's earned total">
            <div class="text-2xl font-bold text-primary">{{ number_format($widgets['weekly_payroll_amount'], 2) }}</div>
        </x-dashboard-card>
        <x-dashboard-card title="Pending Payment" description="This week's remaining total">
            <div class="text-2xl font-bold text-primary">{{ number_format($widgets['pending_weekly_payment'], 2) }}</div>
        </x-dashboard-card>
    </div>

    <x-dashboard-card title="Filters" description="Search and filter employees">
        <form method="GET" action="{{ route('employees.index') }}" class="grid w-full grid-cols-1 gap-4 md:grid-cols-4">
            <label class="form-control w-full">
                <span class="label-text font-semibold">Name</span>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Search employees" class="input input-bordered w-full">
            </label>
            <label class="form-control w-full">
                <span class="label-text font-semibold">Role</span>
                <select name="role" class="select select-bordered w-full">
                    <option value="">All roles</option>
                    @foreach (\App\Enums\EmployeeRole::cases() as $roleOption)
                        <option value="{{ $roleOption->value }}" @selected($filters['role'] === $roleOption->value)>{{ $roleOption->label() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control w-full">
                <span class="label-text font-semibold">Status</span>
                <select name="status" class="select select-bordered w-full">
                    <option value="">All statuses</option>
                    @foreach (\App\Enums\EmployeeStatus::cases() as $statusOption)
                        <option value="{{ $statusOption->value }}" @selected($filters['status'] === $statusOption->value)>{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-end gap-2">
                <x-action-button type="submit" variant="primary">Apply</x-action-button>
                <a href="{{ route('employees.index') }}" class="btn btn-ghost">Reset</a>
            </div>
        </form>
    </x-dashboard-card>
    

    <x-dashboard-card title="Employees" description="All employees">
        <div class="w-full overflow-x-auto">
            @if ($employees->isNotEmpty())
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Daily Laberi</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Earned</th>
                            <th>Paid</th>
                            <th>Remaining</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            <tr>
                                <td>{{ $employee->id }}</td>
                                <td class="font-semibold">{{ $employee->name }}</td>
                                <td>{{ $employee->role->label() }}</td>
                                <td>{{ number_format((float) $employee->daily_laberi, 2) }}</td>
                                <td>{{ $employee->phone_number }}</td>
                                <td>
                                    <span class="badge {{ $employee->status->value === 'active' ? 'badge-success' : 'badge-ghost' }}">{{ $employee->status->label() }}</span>
                                </td>
                                <td>{{ number_format((float) $employee->lifetime_earned, 2) }}</td>
                                <td>{{ number_format((float) $employee->lifetime_paid, 2) }}</td>
                                <td class="font-semibold">{{ number_format((float) $employee->lifetime_remaining, 2) }}</td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('employees.show', $employee) }}">
                                            <x-action-button type="submit" variant="secondary" size="sm">View</x-action-button>
                                        </a>
                                        @if (in_array(auth()->user()?->role, ['super_admin', 'admin'], true))
                                            <a href="{{ route('employees.edit', $employee) }}">
                                                <x-action-button type="submit" variant="secondary" size="sm"><x-feathericon-edit class="w-4 h-4" /></x-action-button>
                                            </a>
                                            <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm('Delete this employee?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-action-button type="submit" variant="error" size="sm"><x-heroicon-o-trash class="w-4 h-4" /></x-action-button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $employees->links() }}
                </div>
            @else
                <x-empty-state title="No employees found" message="No employees match the current filters." />
            @endif
        </div>
    </x-dashboard-card>
</x-app-layout>
