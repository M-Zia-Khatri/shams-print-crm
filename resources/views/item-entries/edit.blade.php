<x-app-layout>
    <x-section-title title="Edit Item Entry"
        subtitle="Update entry details. Uploading a new image replaces the current Cloudinary image after the new upload succeeds." />

    @if ($errors->any())
        <div class="alert alert-error shadow-sm mb-4">
            <span>Please review the highlighted fields and try again.</span>
        </div>
    @endif

    <x-dashboard-card title="{{ $itemEntry->lart_number }}"
        description="Total amount is recomputed automatically when saved.">
        <form method="POST" action="{{ route('item-entries.update', $itemEntry) }}" enctype="multipart/form-data"
            class="w-full space-y-4">
            @csrf
            @method('PUT')

            <x-form-fields :entry="$itemEntry" :image-required="false" />

            <div class="flex justify-end gap-2">
                <a href="{{ route('item-entries.index') }}" class="btn btn-ghost">Cancel</a>
                <x-action-button type="submit" variant="primary">Update Entry</x-action-button>
            </div>
        </form>
    </x-dashboard-card>
</x-app-layout>
