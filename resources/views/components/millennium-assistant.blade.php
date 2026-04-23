{{-- Asistente Ovi: flotante en layout autenticado; respuestas vía OpenRouter (servidor). --}}
<div
    x-data="millenniumAssistant({
        sendUrl: @js(route('assistant.chat')),
        pageRoute: @js(Route::currentRouteName()),
    })"
    class="pointer-events-none fixed bottom-24 right-6 z-50 flex flex-col items-end gap-2"
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-cloak
        class="pointer-events-auto mb-1 w-[min(100vw-2rem,22rem)] max-h-[min(70vh,28rem)] flex flex-col overflow-hidden rounded-xl border border-millennium-dark/15 bg-white shadow-xl"
    >
        <div class="flex items-center justify-between gap-2 border-b border-millennium-dark/10 bg-gradient-to-r from-millennium-sand/40 to-white px-3 py-2">
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-millennium-dark">Ovi — Millennium</p>
                <p class="text-xs text-millennium-dark/60">Ayuda sobre el sistema</p>
            </div>
            <button
                type="button"
                class="rounded-lg p-1 text-millennium-dark/50 hover:bg-millennium-dark/5 hover:text-millennium-dark"
                @click="open = false"
                aria-label="Cerrar asistente"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex min-h-0 flex-1 flex-col">
            <div
                x-ref="msgBox"
                class="max-h-[min(45vh,18rem)] space-y-2 overflow-y-auto px-3 py-2 text-sm"
            >
                <template x-if="messages.length === 0">
                    <p class="rounded-lg bg-millennium-sand/30 px-2 py-2 text-millennium-dark/80">
                        Preguntá por menús (Cobranza, Reportes, facturas…) o por el paso a paso. No tengo acceso a montos reales: te indico dónde verlos en el sistema.
                    </p>
                </template>
                <template x-for="(m, idx) in messages" :key="idx">
                    <div
                        class="rounded-lg px-2 py-1.5"
                        :class="m.role === 'user'
                            ? 'ml-4 bg-millennium-dark/90 text-white'
                            : (m.isError ? 'mr-4 border border-red-200 bg-red-50 text-red-900' : 'mr-4 bg-millennium-sand/40 text-millennium-dark')"
                    >
                        <p class="whitespace-pre-wrap break-words" x-text="m.text"></p>
                    </div>
                </template>
                <template x-if="loading">
                    <p class="text-xs text-millennium-dark/50">Escribiendo…</p>
                </template>
            </div>
            <div class="border-t border-millennium-dark/10 p-2">
                <label class="sr-only" for="ovi-input">Mensaje para Ovi</label>
                <textarea
                    id="ovi-input"
                    x-model="input"
                    @keydown.enter.prevent="if (!$event.shiftKey) { send(); }"
                    rows="2"
                    placeholder="Escribí tu duda…"
                    class="mb-2 w-full resize-none rounded-lg border border-millennium-dark/15 px-2 py-1.5 text-sm text-millennium-dark placeholder-millennium-dark/40 focus:border-millennium-dark/30 focus:outline-none focus:ring-1 focus:ring-millennium-dark/20"
                ></textarea>
                <button
                    type="button"
                    @click="send()"
                    :disabled="loading"
                    class="w-full rounded-lg bg-millennium-dark px-3 py-2 text-sm font-medium text-white shadow hover:bg-millennium-dark/90 disabled:opacity-50"
                >
                    Enviar
                </button>
            </div>
        </div>
    </div>

    <button
        type="button"
        data-chatbot-trigger="true"
        @click="toggle()"
        class="pointer-events-auto flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-millennium-dark via-millennium-dark/90 to-[#2d6a4f] text-2xl shadow-lg transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-millennium-dark/30"
        aria-label="Hablar con Ovi"
    >
        <span class="relative inline-flex">
            <span role="img" aria-hidden="true">🤖</span>
            <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-white bg-emerald-400"></span>
        </span>
    </button>
</div>
