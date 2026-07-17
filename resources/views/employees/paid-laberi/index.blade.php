<x-app-layout>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <x-section-title title="{{ $employee->name }} — Paid Laberi" subtitle="Payment history for this employee." />
        @if (in_array(auth()->user()?->role, ['super_admin', 'admin'], true))
            <a href="{{ route('employees.paid-laberi.create', $employee) }}">
                <x-action-button type="submit" variant="primary">Add Payment</x-action-button>
            </a>
        @endif
    </div>

    @if (session('status'))
        <div class="alert alert-success shadow-sm">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <x-dashboard-card title="Filters" description="Filter payments by date range">
        <x-date-range-filter :start-date="$filters['start_date']" :end-date="$filters['end_date']" range="custom" />
    </x-dashboard-card>

    <x-dashboard-card title="Payments" description="All recorded payments.">
        <div class="w-full overflow-x-auto">
            @if ($payments->isNotEmpty())
                <table class="table table-zebra w-full">
                    <thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Reference</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            <tr>
                                <td>{{ $payment->paid_date->format('M d, Y') }}</td>
                                <td>{{ $payment->payment_type->label() }}</td>
                                <td>{{ number_format((float) $payment->amount, 2) }}</td>
                                <td>{{ $payment->reference_no }}</td>
                                <td>
                                    @if (in_array(auth()->user()?->role, ['super_admin', 'admin'], true))
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('employees.paid-laberi.edit', [$employee, $payment]) }}">
                                                <x-action-button type="submit" variant="secondary" size="sm"><x-feathericon-edit class="w-4 h-4" /></x-action-button>
                                            </a>
                                            <form method="POST" action="{{ route('employees.paid-laberi.destroy', [$employee, $payment]) }}" onsubmit="return confirm('Delete this payment?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-action-button type="submit" variant="error" size="sm"><x-heroicon-o-trash class="w-4 h-4" /></x-action-button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">{{ $payments->links() }}</div>
            @else
                <x-empty-state title="No payments found" message="No payments match the current filters." />
            @endif
        </div>
    </x-dashboard-card>
</x-app-layout>
