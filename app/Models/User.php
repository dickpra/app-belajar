<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse; // 👈 Pastikan ini di-import

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // 1. Jika GURU login/nyasar ke link Admin -> Langsung lempar ke Teacher
        if ($panel->getId() === 'admin' && $this->role === 'teacher') {
            throw new HttpResponseException(new RedirectResponse(url('/teacher')));
        }

        // 2. Jika ADMIN login/nyasar ke link Guru -> Langsung lempar ke Admin
        if ($panel->getId() === 'teacher' && $this->role === 'admin') {
            throw new HttpResponseException(new RedirectResponse(url('/admin')));
        }

        // 3. Beri izin masuk HANYA jika jalurnya dan role-nya sama-sama cocok
        if ($panel->getId() === 'admin' && $this->role === 'admin') {
            return true;
        }

        if ($panel->getId() === 'teacher' && $this->role === 'teacher') {
            return true;
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }
}