<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Umbral de vencimiento
    |--------------------------------------------------------------------------
    |
    | Horas desde la asignación sin gestión (estado_gestion = Nuevo) tras las
    | cuales un lead se marca como "vencido" en la vista del admin.
    |
    */
    'staleness_hours' => env('LEADS_STALENESS_HOURS', 48),

    /*
    |--------------------------------------------------------------------------
    | Ventana de reparto automático
    |--------------------------------------------------------------------------
    |
    | Días hacia atrás que cuentan como "reciente" al elegir a qué concesionario
    | asignar el próximo lead (leads_recientes / peso_asignacion). Evita que un
    | concesionario recién activado tenga que "alcanzar" el histórico completo
    | de los demás antes de empezar a recibir su parte proporcional.
    |
    */
    'assignment_window_days' => env('LEADS_ASSIGNMENT_WINDOW_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Sincronización con Google Sheets
    |--------------------------------------------------------------------------
    */
    'sync' => [
        'credentials_path' => env('GOOGLE_SHEETS_CREDENTIALS_PATH'),
        'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID'),
        // Nombres de pestaña separados por "|" (no ",", porque un nombre de pestaña
        // puede contener comas, ej. "Expocarshow 2026, Julio. NEW (v3)").
        'sheet_names' => array_filter(array_map(
            'trim',
            explode('|', env('GOOGLE_SHEETS_SHEET_NAMES', env('GOOGLE_SHEETS_SHEET_NAME', 'Sheet1')))
        )),
    ],

];
