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
    | Sincronización con Google Sheets
    |--------------------------------------------------------------------------
    */
    'sync' => [
        'credentials_path' => env('GOOGLE_SHEETS_CREDENTIALS_PATH'),
        'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID'),
        'sheet_name' => env('GOOGLE_SHEETS_SHEET_NAME', 'Sheet1'),
    ],

];
