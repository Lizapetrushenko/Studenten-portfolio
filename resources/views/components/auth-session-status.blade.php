@props(['status'])

@if ($status)
    <div x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 4000)" x-show="visible" x-transition.opacity {{ $attributes->merge(['class' => 'flash-message font-medium text-sm text-green-600']) }}>
        {{ $status }}
    </div>
@endif
