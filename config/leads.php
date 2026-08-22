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
    | Redistribución de leads vencidos sin asesor
    |--------------------------------------------------------------------------
    |
    | Concesionarios (por nombre, en el orden en que reciben el reparto)
    | entre los que se distribuyen en round-robin los leads vencidos y sin
    | asesor de todo el sistema, cuando el admin dispara la redistribución.
    |
    */
    'redistribution' => [
        'target_concesionarios' => [
            'VF Motors',
            'Auto 2 SAS',
            'Puntokar multimarcas SAS',
        ],
    ],

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
