{{-- Millennium / Incapor — CTA principal: `bg-millennium-dark`, focus `millennium-sand` (misma paleta que `app.css` / Tailwind) --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 min-h-[44px] sm:min-h-0 bg-millennium-dark border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:brightness-110 active:brightness-95 focus:outline-none focus:ring-2 focus:ring-millennium-sand focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
