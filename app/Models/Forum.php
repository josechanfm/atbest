<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    use HasFactory;

    protected $fillable = ['organization_id', 'user_id', 'title', 'description'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public static function forUser(User $user =null)
    {
        if ($user) {
            return self::whereIn('organization_id', $user->members->pluck('organization_id'))->get();
        }else{
            $user = $user ?? auth()->user();
            return self::whereIn('organization_id', $user->members->pluck('organization_id'))->get();
        }
    }
    public function contents()
    {
        return $this->hasMany(ForumContent::class);
    }
}
