{{-- Millennium: secundario claro; borde suave marca, sin competir con semánticos (verde/ámbar/rojo) --}}
<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 min-h-[44px] sm:min-h-0 bg-white dark:bg-gray-800 border border-millennium-dark/20 dark:border-millennium-sand/30 rounded-md font-semibold text-xs text-millennium-dark dark:text-millennium-sand uppercase tracking-widest shadow-sm hover:bg-millennium-sand/15 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-millennium-sand focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
