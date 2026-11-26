<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationJoinRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'name',
        'email',
        'phone',
        'reason',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
