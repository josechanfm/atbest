<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',          // 'radio' or 'checkbox'
        'question',
        'image',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'option_e',
        'answer',        // Stored as array/JSON (e.g., ['A'] or ['A', 'C'])
        'reference',
        'status',
        'session_token',
        'user_id',
    ];

    protected $casts = [
        'answer' => 'array',  // Automatically converts JSON <-> Array
        'status' => 'boolean',
    ];

    protected $appends = ['responses_count'];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->uuid = (string) Str::uuid();

            // Set default type to 'radio' if missing
            if (empty($model->type)) {
                $model->type = 'radio';
            }

            if (!isset($model->status)) {
                $model->status = false;
            }

            // Safe auth check for console/seeder environments
            if (auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }

    public function getResponsesCountAttribute()
    {
        // Using count relation loads count directly without pulling all response records into memory
        return $this->responses()->count();
    }

    public function responses()
    {
        return $this->hasMany(PollResponse::class);
    }
}