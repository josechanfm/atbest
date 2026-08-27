<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'user_id',
        'mobile',
        'content',
        'status',
        'response',
        'sent_at',
    ];

    protected $casts = [
        'response' => 'array',
        'sent_at' => 'datetime',
    ];

    // Relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Helper method to mark as sent
    public function markAsSent($response = null)
    {
        $this->update([
            'status' => 'sent',
            'response' => $response,
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed($response = null)
    {
        $this->update([
            'status' => 'failed',
            'response' => $response,
        ]);
    }
}