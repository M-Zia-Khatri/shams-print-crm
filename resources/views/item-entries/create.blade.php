<x-app-layout>
    <x-section-title title="Add Item Entries" subtitle="Submit one or more item entries. Total amount is computed automatically after submission." />

    @if($errors->any())
        <div class="alert alert-error shadow-sm mb-4">
            <span>Please review the highlighted fields and try again.</span>
        </div>
    @endif

    <x-dashboard-card title="New Entries" description="Use Add More Entry to submit multiple entries in one batch.">
        <form method="POST" action="{{ route('item-entries.store') }}" enctype="multipart/form-data" class="w-full space-y-4" id="item-entry-form">
            @csrf

            <div id="entries-container" class="space-y-4">
                <x-item-entries.form-fields prefix="entries[0]" :index="0" />
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                <x-action-button type="button" variant="outline" id="add-entry-button">Add More Entry</x-action-button>

                <div class="flex gap-2 justify-end">
                    <a href="{{ route('item-entries.index') }}" class="btn btn-ghost">Cancel</a>
                    <x-action-button type="submit" variant="primary">Save Entries</x-action-button>
                </div>
            </div>
        </form>
    </x-dashboard-card>

    <template id="entry-template">
        <x-item-entries.form-fields prefix="entries[__INDEX__]" :index="'__INDEX__'" />
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('entries-container');
            const template = document.getElementById('entry-template');
            const addButton = document.getElementById('add-entry-button');
            let entryIndex = 1;

            addButton.addEventListener('click', function () {
                const wrapper = document.createElement('div');
                wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', entryIndex);
                const entry = wrapper.firstElementChild;
                const removeButton = document.createElement('button');

                removeButton.type = 'button';
                removeButton.className = 'btn btn-ghost btn-sm text-error mt-2';
                removeButton.textContent = 'Remove Entry';
                removeButton.addEventListener('click', function () {
                    entry.remove();
                    removeButton.remove();
                });

                container.appendChild(entry);
                container.appendChild(removeButton);
                entryIndex += 1;
            });
        });
    </script>
</x-app-layout>
