<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    protected $fillable = [
        'token',
        'inviter_id',
        'email',
        'expires_at',
        'used_at',
        'used_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    // ---------- Relationships ----------

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    // ---------- Scopes ----------

    public function scopeUsable($query)
    {
        return $query->whereNull('used_at')
                     ->where('expires_at', '>', now());
    }

    // ---------- Helpers ----------

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function markAsUsed(User $user): void
    {
        $this->update([
            'used_at' => now(),
            'used_by' => $user->id,
        ]);
    }
    public function markAsSent(): void
    {
        $this->update([
            'sent_at' => now(),
            'sent_by' => auth()->id(),
        ]);
    }

    public function getShareUrlAttribute(): string
    {
        return route('invitations.show', ['token' => $this->token]);
    }
}