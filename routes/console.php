<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('leads:sync-sheet')->everyMinute()->withoutOverlapping();
Schedule::command('leads:notify-vencidos')->everyMinute()->withoutOverlapping();
Schedule::command('usuarios:sync-sheet')->hourly()->withoutOverlapping();
