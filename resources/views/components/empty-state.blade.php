@props([
    'title' => 'No activity found',
    'message' => 'There is no history or data to display right now.',
    'icon' => null
])

<div class="flex flex-col items-center justify-center py-10 px-6 text-center bg-base-100/30 rounded-xl border border-dashed border-base-300">
    <div class="p-4 bg-base-200/50 rounded-full mb-3 text-base-content/40">
        @if($icon)
            {!! $icon !!}
        @else
            <!-- Default Empty Activity SVG -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
        @endif
    </div>
    <h3 class="text-base font-bold text-base-content">{{ $title }}</h3>
    <p class="text-sm text-base-content/60 max-w-sm mt-1 mb-4 leading-relaxed">{{ $message }}</p>
    @if(isset($slot) && $slot->isNotEmpty())
        <div>
            {{ $slot }}
        </div>
    @endif
</div>
