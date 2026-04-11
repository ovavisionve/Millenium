{{-- Millennium: peligro — rojo apagado (semántico), no confundir con marca arena/marrón --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 min-h-[44px] sm:min-h-0 bg-rose-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-rose-600 active:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
