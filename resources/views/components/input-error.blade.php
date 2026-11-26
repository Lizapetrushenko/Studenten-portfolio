@props(['messages'])

@if ($messages)
    <ul x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 5000)" x-show="visible" x-transition.opacity role="alert" aria-live="assertive" {{ $attributes->merge(['class' => 'text-sm text-red-600 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
