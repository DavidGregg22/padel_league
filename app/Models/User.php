<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'is_admin'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Global super-admin (can manage clubs themselves). */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /** Is this user an admin of the given club? */
    public function isClubAdmin(Club $club): bool
    {
        return $this->clubs()
            ->wherePivot('club_id', $club->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }

    /** Is this user a member (any role) of the given club? */
    public function belongsToClub(Club $club): bool
    {
        return $this->clubs()->wherePivot('club_id', $club->id)->exists();
    }
}
