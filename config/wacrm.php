<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Integración con wacrm (CRM de WhatsApp)
    |--------------------------------------------------------------------------
    |
    | Empuja los leads (solo teléfono + nombre) como contactos a wacrm para
    | poder atenderlos por WhatsApp. Apagado por defecto: no se activa hasta
    | que haya credenciales reales configuradas y se confirme que se probó.
    |
    */

    'enabled' => env('WACRM_SYNC_ENABLED', false),

    'url' => env('WACRM_API_URL'),

    'api_key' => env('WACRM_API_KEY'),

];
