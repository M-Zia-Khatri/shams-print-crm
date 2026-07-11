@props([
    'prefix' => '',
    'entry' => null,
    'index' => 0,
    'imageRequired' => true,
])

@php
    $field = fn (string $name) => $prefix === '' ? $name : "{$prefix}[{$name}]";
    $error = fn (string $name) => $prefix === '' ? $name : "entries.{$index}.{$name}";
@endphp

<div class="entry-fields grid grid-cols-1 md:grid-cols-2 gap-4 rounded-xl border border-base-300 bg-base-100/60 p-4" data-entry-fields>
    <label class="form-control w-full">
        <span class="label-text font-semibold">Lart Number</span>
        <input type="text" name="{{ $field('lart_number') }}" value="{{ old($error('lart_number'), $entry?->lart_number) }}" class="input input-bordered w-full" required maxlength="255">
        @error($error('lart_number'))<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    </label>

    <label class="form-control w-full">
        <span class="label-text font-semibold">Party Name</span>
        <input type="text" name="{{ $field('client_business_name') }}" value="{{ old($error('client_business_name'), $entry?->client_business_name) }}" class="input input-bordered w-full" required maxlength="255">
        @error($error('client_business_name'))<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    </label>

    <label class="form-control w-full md:col-span-2">
        <span class="label-text font-semibold">Description</span>
        <input type="text" name="{{ $field('description') }}" value="{{ old($error('description'), $entry?->description) }}" class="input input-bordered w-full" required maxlength="255">
        @error($error('description'))<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    </label>

    <label class="form-control w-full md:col-span-2">
        <span class="label-text font-semibold">Image</span>
        <input type="file" name="{{ $field('image') }}" class="file-input file-input-bordered w-full" accept="image/jpeg,image/png,image/webp" @required($imageRequired)>
        @if($entry?->image_url)
            <span class="text-sm text-base-content/60 mt-2">Current image is shown in the preview below. Upload a new image to replace it.</span>
            <img src="{{ $entry->image_url }}" alt="{{ $entry->lart_number }}" class="mt-3 h-24 w-24 rounded-lg object-cover border border-base-300">
        @endif
        @error($error('image'))<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    </label>

    <label class="form-control w-full">
        <span class="label-text font-semibold">Darjan</span>
        <input type="number" name="{{ $field('darjan') }}" value="{{ old($error('darjan'), $entry?->darjan) }}" class="input input-bordered w-full" required min="0" step="1">
        @error($error('darjan'))<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    </label>

    <label class="form-control w-full">
        <span class="label-text font-semibold">Total Color</span>
        <input type="number" name="{{ $field('total_color') }}" value="{{ old($error('total_color'), $entry?->total_color) }}" class="input input-bordered w-full" required min="0" step="1">
        @error($error('total_color'))<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    </label>

    <label class="form-control w-full">
        <span class="label-text font-semibold">Size</span>
        <input type="text" name="{{ $field('size_description') }}" value="{{ old($error('size_description'), $entry?->size_description) }}" class="input input-bordered w-full" required maxlength="255">
        @error($error('size_description'))<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    </label>

    <label class="form-control w-full">
        <span class="label-text font-semibold">Total Rate</span>
        <input type="number" name="{{ $field('total_rate') }}" value="{{ old($error('total_rate'), $entry?->total_rate) }}" class="input input-bordered w-full" required min="0" step="0.01">
        @error($error('total_rate'))<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    </label>
</div>
