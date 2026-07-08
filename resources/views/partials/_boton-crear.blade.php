{{--
    Parámetros:
    $href   — URL de destino
    $texto  — texto completo (ej: "Nuevo Cliente")
--}}
<a href="{{ $href }}"
    title="{{ $texto }}"
    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 px-4 py-2.5 rounded-xl font-medium transition text-sm">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
    <span>{{ $texto }}</span>
</a>
