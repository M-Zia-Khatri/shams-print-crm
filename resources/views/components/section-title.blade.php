@props([
    'title' => '',
    'subtitle' => ''
])

<div class="mb-6">
    <h2 class="text-xl font-bold tracking-tight text-base-content">{{ $title }}</h2>
    @if($subtitle)
        <p class="text-sm text-base-content/60 mt-1 font-normal leading-relaxed">{{ $subtitle }}</p>
    @endif
</div>
