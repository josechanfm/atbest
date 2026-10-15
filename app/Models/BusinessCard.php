<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BusinessCard extends Model
{
    protected $table = 'business_cards';

    protected $fillable = [
        'user_id',
        'content',
        'bio',
    ];

    protected $casts = [
        'content' => 'json',
        'bio'     => 'json',
    ];

    protected static function booted(): void
    {
        static::creating(function (BusinessCard $businessCard) {
            $businessCard->uuid = (string) Str::uuid();
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}