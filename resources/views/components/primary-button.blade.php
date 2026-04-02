<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-opta-teal-dark border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-opta-teal-dark/90 focus:bg-opta-teal-dark active:bg-opta-teal-dark focus:outline-none focus:ring-2 focus:ring-opta-teal-light focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
