<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Services\LeadNotifier;
use Illuminate\Console\Command;

class NotifyVencidoLeads extends Command
{
    protected $signature = 'leads:notify-vencidos';

    protected $description = 'Notifica a los admins los leads que acaban de cruzar el umbral de vencido';

    public function handle(LeadNotifier $notifier): int
    {
        $leads = Lead::vencido()
            ->whereNull('vencido_notified_at')
            ->get();

        foreach ($leads as $lead) {
            $notifier->notifyVencido($lead);
            $lead->update(['vencido_notified_at' => now()]);
        }

        $this->info("{$leads->count()} lead(s) vencido(s) notificados.");

        return self::SUCCESS;
    }
}
