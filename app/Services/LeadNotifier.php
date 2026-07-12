<?php

namespace App\Services;

use App\Models\AsesorComercial;
use App\Models\Concesionario;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\NuevoLeadAsignado;
use Illuminate\Support\Facades\Notification;

class LeadNotifier
{
    public function notifyConcesionario(Concesionario $concesionario, Lead $lead): void
    {
        $usuarios = User::where('rol', 'concesionario')
            ->where('concesionario_id', $concesionario->id)
            ->get()
            ->merge($this->admins());

        Notification::send($usuarios, new NuevoLeadAsignado($lead));
    }

    public function notifyAsesor(AsesorComercial $asesor, Lead $lead): void
    {
        $usuarios = User::where('rol', 'asesor')
            ->where('asesor_comercial_id', $asesor->id)
            ->get()
            ->merge($this->admins());

        Notification::send($usuarios, new NuevoLeadAsignado($lead));
    }

    private function admins(): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('rol', 'admin')->get();
    }
}
