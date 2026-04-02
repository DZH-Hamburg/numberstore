@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-opta-teal-dark text-start text-base font-medium text-opta-teal-dark bg-opta-teal-light/20 focus:outline-none transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-opta-grey hover:text-opta-teal-dark hover:bg-opta-teal-light/10 hover:border-opta-teal-light focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
