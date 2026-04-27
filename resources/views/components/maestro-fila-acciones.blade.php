{{-- Millennium — acciones de fila en tablas de maestros (mismo look que NUEVO + variante arena en Eliminar). --}}
@props([
    'editUrl',
    'deleteUrl' => null,
    'deleteConfirm' => '¿Confirma eliminar este registro?',
    'deleteAriaLabel' => 'Eliminar',
])

<div class="inline-flex max-w-full flex-nowrap items-center justify-end gap-2">
    <a
        href="{{ $editUrl }}"
        class="inline-flex shrink-0 items-center gap-1.5 rounded-sm border border-transparent bg-millennium-dark px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:brightness-110 active:brightness-95 focus:outline-none focus:ring-2 focus:ring-millennium-sand focus:ring-offset-2 min-h-[44px] sm:min-h-0 dark:focus:ring-offset-gray-800 !bg-millennium-dark"
    >
        <svg class="h-3.5 w-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
        </svg>
        Editar
    </a>
    @if (! empty($deleteUrl))
        <form action="{{ $deleteUrl }}" method="post" class="inline-flex shrink-0" onsubmit='return confirm(@json($deleteConfirm));'>
            @csrf @method('DELETE')
            <button
                type="submit"
                class="inline-flex min-h-[44px] shrink-0 appearance-none items-center gap-1.5 rounded-sm border px-4 py-2 text-xs font-semibold uppercase tracking-widest shadow-sm transition duration-150 ease-in-out hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-millennium-dark focus:ring-offset-2 sm:min-h-0 dark:focus:ring-offset-gray-800"
                style="background-color:#DDB387;border-color:rgba(50,29,23,0.35);color:#321D17;"
                aria-label="{{ $deleteAriaLabel }}"
            >
                <svg class="h-3.5 w-3.5 shrink-0" style="color:#321D17" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.933c0-1.18-.09-1.2-.36-1.8-.18-.3-.5-.67-.8-.8-.3-.12-.5-.1-1.2-.1H9.1c-.7 0-.9.02-1.2.1-.3.13-.62.5-.8.8-.27.6-.36.6-.36 1.8v.933m-3.6 0a48.11 48.11 0 0 0 3.6 0" />
                </svg>
                Eliminar
            </button>
        </form>
    @endif
</div>
