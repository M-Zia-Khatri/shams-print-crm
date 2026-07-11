<x-app-layout>
    <x-section-title title="Add Item Entries" subtitle="Submit one or more item entries. Total amount is computed automatically after submission." />

    @if($errors->any())
        <div class="alert alert-error shadow-sm mb-4">
            <span>Please review the highlighted fields and try again.</span>
        </div>
    @endif

    <div id="draft-restored-alert" class="alert alert-info shadow-sm mb-4 hidden items-center justify-between">
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>Draft restored successfully.</span>
        </div>
        <button type="button" id="discard-draft-button" class="btn btn-sm btn-ghost">Discard Draft</button>
    </div>

    <script type="application/json" id="party-names-data">
        {!! $partyNames->toJson() !!}
    </script>
    
    <x-dashboard-card title="New Entries" description="Use Add More Entry to submit multiple entries in one batch.">
        <form method="POST" action="{{ route('item-entries.store') }}" enctype="multipart/form-data"
            class="w-full space-y-4" id="item-entry-form">
            @csrf

            <div id="entries-container" class="space-y-4">
                <x-form-fields prefix="entries[0]" :index="0" />
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
        <x-form-fields prefix="entries[__INDEX__]" :index="'__INDEX__'" />
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
