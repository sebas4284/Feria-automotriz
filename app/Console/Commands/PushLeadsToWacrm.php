<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Services\WacrmClient;
use Illuminate\Console\Command;

class PushLeadsToWacrm extends Command
{
    protected $signature = 'leads:push-to-wacrm';

    protected $description = 'Empuja (una sola vez) todos los leads con teléfono a wacrm como contactos, para los que ya existían antes de activar la sincronización automática';

    public function handle(WacrmClient $wacrmClient): int
    {
        if (! config('wacrm.enabled')) {
            $this->error('WACRM_SYNC_ENABLED está apagado. Actívalo en el .env antes de correr este comando.');

            return self::FAILURE;
        }

        $leads = Lead::whereNotNull('phone_number')->where('phone_number', '!=', '')->get();

        $this->info("Enviando {$leads->count()} leads a wacrm...");

        $bar = $this->output->createProgressBar($leads->count());

        foreach ($leads as $lead) {
            $wacrmClient->syncContact($lead);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Listo.');

        return self::SUCCESS;
    }
}
