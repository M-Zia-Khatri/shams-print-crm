@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => '',
    'icon' => null
])

@php
    $buttonClass = 'btn ' . match($variant) {
        'primary' => 'btn-primary text-primary-content',
        'secondary' => 'btn-secondary text-secondary-content',
        'accent' => 'btn-accent text-accent-content',
        'ghost' => 'btn-ghost',
        'outline' => 'btn-outline',
        'info' => 'btn-info',
        'success' => 'btn-success',
        'warning' => 'btn-warning',
        'error' => 'btn-error',
        default => 'btn-primary text-primary-content'
    };

    if ($size) {
        $buttonClass .= ' ' . match($size) {
            'xs' => 'btn-xs',
            'sm' => 'btn-sm',
            'lg' => 'btn-lg',
            default => ''
        };
    }
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $buttonClass . ' gap-2 transition-all duration-200 active:scale-95']) }}>
    @if($icon)
        <span class="inline-flex shrink-0 w-4 h-4 text-current justify-center items-center">
            {!! $icon !!}
        </span>
    @endif
    <span>{{ $slot }}</span>
</button>
