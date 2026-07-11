<div
    x-data="{
        ...pushBanner('{{ config('webpush.vapid.public_key') }}'),
        dismissed: localStorage.getItem('push-banner-dismissed') === '1',
        dismiss() {
            this.dismissed = true;
            localStorage.setItem('push-banner-dismissed', '1');
        },
    }"
    x-show="!dismissed && (status === 'default' || status === 'unsupported-ios')"
    x-cloak
    class="bg-blue-600 text-white px-4 py-2.5 text-sm flex items-center justify-between gap-3"
>
    <template x-if="status === 'default'">
        <div class="flex items-center justify-between gap-3 w-full">
            <span>Activa las notificaciones para enterarte al instante cuando te llegue un lead nuevo.</span>
            <div class="flex items-center gap-2 shrink-0">
                <button @click="subscribe()" :disabled="loading"
                    class="bg-white text-blue-700 px-3 py-1 rounded-lg text-xs font-semibold hover:bg-blue-50 transition disabled:opacity-60">
                    <span x-show="!loading">Activar</span>
                    <span x-show="loading">Activando...</span>
                </button>
                <button @click="dismiss()" class="text-blue-200 hover:text-white px-1" title="Cerrar">✕</button>
            </div>
        </div>
    </template>

    <template x-if="status === 'unsupported-ios'">
        <div class="flex items-center justify-between gap-3 w-full">
            <span>Para recibir notificaciones en iPhone: toca <strong>Compartir</strong> y luego <strong>"Agregar a inicio"</strong>, y abre la app desde ahí.</span>
            <button @click="dismiss()" class="text-blue-200 hover:text-white px-1 shrink-0" title="Cerrar">✕</button>
        </div>
    </template>
</div>
