<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invitation extends Model
{
    protected $fillable = ['club_id', 'email', 'token', 'accepted_at'];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invitation $invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(48);
            }
        });
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function isPending(): bool
    {
        return is_null($this->accepted_at);
    }
}
