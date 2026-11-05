@props(['title' => null])
<x-app-layout :title="$title" {{ $attributes }}>
    @isset($header)
        <x-slot:header>{{ $header }}</x-slot:header>
    @endisset
    {{ $slot }}
</x-app-layout>
