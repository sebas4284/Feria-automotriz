<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\RestablecerContrasena;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Concesionario;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasPushSubscriptions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'concesionario_id',
        'asesor_comercial_id',
        'rol',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function concesionario()
{
    return $this->belongsTo(
        Concesionario::class
    );
}

    public function asesorComercial()
    {
        return $this->belongsTo(AsesorComercial::class);
    }

    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function isConcesionario(): bool
    {
        return $this->rol === 'concesionario';
    }

    public function isAsesor(): bool
    {
        return $this->rol === 'asesor';
    }

    public function isStaff(): bool
    {
        return $this->rol === 'staff';
    }

    public function concesionarioIdPropio(): ?int
    {
        return $this->concesionario_id ?? $this->asesorComercial?->concesionario_id;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new RestablecerContrasena($token));
    }
}
