@props(['status', 'size' => 'h-36 w-36'])
@php
    $statusPhoto = match ($status) {
        'nieuw' => ['images/nieuw.svg', 'Nieuwe student'],
        'herkansen' => ['images/herkansen.png', 'Herkansen'],
        'geslaagd' => ['images/geslaagd.png', 'Geslaagd'],
        'weg' => ['images/weg.svg', 'Weg'],
        default => null,
    };
@endphp
@if($statusPhoto)
    <img src="{{ asset($statusPhoto[0]) }}" alt="{{ $statusPhoto[1] }}" {{ $attributes->merge(['class' => 'mx-auto rounded-full object-contain '.$size]) }}>
@endif
