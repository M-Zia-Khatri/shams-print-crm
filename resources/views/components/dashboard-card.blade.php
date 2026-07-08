@props([
    'icon' => null,
    'title' => '',
    'description' => ''
])

<div class="card bg-base-100 hover:bg-base-200/50 shadow-sm hover:shadow-md border border-base-300 transition-all duration-300 transform hover:-translate-y-1 rounded-xl">
    <div class="card-body p-6">
        <div class="flex items-start gap-4">
            @if($icon)
                <div class="p-3 bg-primary/10 text-primary rounded-xl shrink-0 mt-0.5">
                    {!! $icon !!}
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <h3 class="card-title text-base font-bold text-base-content leading-tight mb-1">{{ $title }}</h3>
                <p class="text-sm text-base-content/70 font-normal leading-relaxed">{{ $description }}</p>
            </div>
        </div>
        @if(isset($slot) && $slot->isNotEmpty())
            <div class="card-actions justify-end mt-4">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
