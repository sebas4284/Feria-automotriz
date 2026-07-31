{{--
    Parámetros:
    $venta     — instancia de Venta
    $clase     — clases CSS del botón que abre el modal
    $contenido — HTML/texto dentro del botón (ícono o "Eliminar")
--}}

<div x-data="{ open: false, motivo: '' }" class="contents">
    <button type="button" @click="open = true" title="Eliminar" class="{{ $clase }}">
        {!! $contenido !!}
    </button>

    <div x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
        @keydown.escape.window="open = false">
        <div @click.outside="open = false" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-1">Eliminar venta</h3>
            <p class="text-sm text-gray-400 mb-4">Esta acción no se puede deshacer. Escribe el motivo para dejar registro.</p>
            <form action="{{ route('ventas.destroy', $venta) }}" method="POST">
                @csrf
                @method('DELETE')
                <textarea name="motivo" x-model="motivo" rows="3" placeholder="Motivo de la eliminación"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm mb-4 focus:outline-none focus:border-blue-500"></textarea>
                <div class="flex gap-2">
                    <button type="button" @click="open = false"
                        class="flex-1 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-sm transition">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="motivo.trim() === ''"
                        class="flex-1 py-2 rounded-xl bg-red-600 hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed text-sm text-white transition">
                        Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
