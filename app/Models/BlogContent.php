<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogContent extends Model
{
    use HasFactory;

    protected $fillable = ['blog_id', 'reply_id', 'title', 'content', 'user_id'];

    protected $appends = [
        'user',
        'reply_contents',
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
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
        return BlogContent::where('reply_id', $this->id)->orderBy('created_at', 'desc')->get();
    }
    
    public function parent()
    {
        return $this->belongsTo(BlogContent::class, 'reply_id')->orderBy('created_at');
    }
    public function children()
    {
        return $this->hasMany(BlogContent::class, 'reply_id')->orderBy('created_at');
    }
}
