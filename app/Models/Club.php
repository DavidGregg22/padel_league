<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Club extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        static::creating(function (Club $club) {
            if (empty($club->slug)) {
                $club->slug = Str::slug($club->name);
            }
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'club_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function admins()
    {
        return $this->users()->wherePivot('role', 'admin');
    }

    public function members()
    {
        return $this->users()->wherePivot('role', 'member');
    }

    public function seasons()
    {
        return $this->hasMany(Season::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function activeSeason()
    {
        return $this->seasons()->where('active', true)->first();
    }
}
