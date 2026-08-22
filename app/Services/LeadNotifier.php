<?php

namespace App\Services;

use App\Models\AsesorComercial;
use App\Models\Concesionario;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadsRedistribuidos;
use App\Notifications\LeadVencido;
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

    /**
     * Notifica una sola vez por concesionario cuántos leads le tocaron en un
     * reparto masivo, en vez de una notificación por cada lead individual
     * (evita disparar cientos de envíos sincrónicos de webpush en un mismo
     * request cuando el lote es grande).
     */
    public function notifyLoteRedistribuido(Concesionario $concesionario, int $cantidad): void
    {
        $usuarios = User::where('rol', 'concesionario')
            ->where('concesionario_id', $concesionario->id)
            ->get()
            ->merge($this->admins());

        Notification::send($usuarios, new LeadsRedistribuidos($cantidad));
    }

    public function notifyAsesor(AsesorComercial $asesor, Lead $lead): void
    {
        $usuarios = User::where('rol', 'asesor')
            ->where('asesor_comercial_id', $asesor->id)
            ->get()
            ->merge($this->admins());

        Notification::send($usuarios, new NuevoLeadAsignado($lead));
    }

    public function notifyVencido(Lead $lead): void
    {
        Notification::send($this->admins(), new LeadVencido($lead));
    }

    private function admins(): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('rol', 'admin')->get();
    }
}
