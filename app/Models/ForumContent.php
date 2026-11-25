<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumContent extends Model
{
    use HasFactory;

    protected $fillable = ['forum_id', 'reply_id', 'title', 'content', 'user_id'];

    protected $appends = [
        'user',
        'reply_contents',
    ];

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getUserAttribute()
    {
        return $this->user()->first();
    }
    public function getReplyContentsAttribute()
    {
        return ForumContent::where('reply_id', $this->id)->orderBy('created_at', 'desc')->get();
    }
    
    public function parent()
    {
        return $this->belongsTo(ForumContent::class, 'reply_id')->orderBy('created_at');
    }
    public function children()
    {
        return $this->hasMany(ForumContent::class, 'reply_id')->orderBy('created_at');
    }
}
