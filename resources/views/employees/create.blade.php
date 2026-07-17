<x-app-layout>
    <x-section-title title="Add Employees" subtitle="Add one or more employees in a single batch." />

    @if ($errors->any())
        <div class="alert alert-error shadow-sm mb-4">
            <span>Please review the highlighted fields and try again.</span>
        </div>
    @endif

    <x-dashboard-card title="New Employees" description="Use Add More Employee to submit multiple employees at once.">
        <form method="POST" action="{{ route('employees.store') }}" class="w-full space-y-4" id="employee-form">
            @csrf

            <div id="employees-container" class="space-y-4">
                @include('employees.partials.employee-fields', ['prefix' => 'employees[0]', 'index' => 0])
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                <x-action-button type="button" variant="outline" id="add-employee-button">Add More Employee</x-action-button>
                <div class="flex gap-2 justify-end">
                    <a href="{{ route('employees.index') }}" class="btn btn-ghost">Cancel</a>
                    <x-action-button type="submit" variant="primary">Save Employees</x-action-button>
                </div>
            </div>
        </form>
    </x-dashboard-card>

    <template id="employee-template">
        @include('employees.partials.employee-fields', ['prefix' => 'employees[__INDEX__]', 'index' => '__INDEX__'])
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('employees-container');
            const template = document.getElementById('employee-template');
            const addButton = document.getElementById('add-employee-button');
            let index = 1;

            addButton.addEventListener('click', function () {
                const wrapper = document.createElement('div');
                wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', index);
                container.appendChild(wrapper.firstElementChild);
                index += 1;
            });
        });
    </script>
</x-app-layout>
