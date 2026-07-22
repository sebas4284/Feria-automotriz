<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sincronización de usuarios/concesionarios desde Google Sheets
    |--------------------------------------------------------------------------
    |
    | Spreadsheet de onboarding donde cada pestaña es un concesionario nuevo
    | con sus usuarios (concesionario/asesor). Reutiliza las mismas
    | credenciales de servicio que el sync de leads.
    |
    */
    'sync' => [
        'credentials_path' => env('GOOGLE_SHEETS_CREDENTIALS_PATH'),
        'spreadsheet_id' => env('GOOGLE_SHEETS_USUARIOS_SPREADSHEET_ID'),
    ],

];
