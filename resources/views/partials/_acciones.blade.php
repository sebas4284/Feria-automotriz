{{--
    Parámetros:
    $modelo        — instancia del modelo
    $ruta          — prefijo de ruta (ej: 'clientes', 'vehiculos')
    $label         — nombre para el confirm de eliminar (ej: 'cliente')
    $sinVer        — (opcional) omite el botón Ver
    $sinEliminar   — (opcional) omite el botón Eliminar
    $labelEditar   — (opcional) tooltip del botón editar (default: 'Editar')
--}}

<div class="flex items-center gap-1.5">

    @unless($sinVer ?? false)
    <a href="{{ route($ruta . '.show', $modelo) }}"
        title="Ver"
        class="p-2.5 sm:p-1.5 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-300 hover:text-white transition">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
    </a>
    @endunless

    <a href="{{ route($ruta . '.edit', $modelo) }}"
        title="{{ $labelEditar ?? 'Editar' }}"
        class="p-2.5 sm:p-1.5 rounded-lg bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 hover:text-blue-300 transition">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
        </svg>
    </a>

    @unless($sinEliminar ?? false)
    <form action="{{ route($ruta . '.destroy', $modelo) }}" method="POST" class="inline">
        @csrf @method('DELETE')
        <button type="submit"
            onclick="return confirm('¿Eliminar {{ $label ?? 'registro' }}?')"
            title="Eliminar"
            class="p-2.5 sm:p-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/40 text-red-400 hover:text-red-300 transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
        </button>
    </form>
    @endunless

</div>
