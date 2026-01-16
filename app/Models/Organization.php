<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AdminUser;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Organization extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable=['parish','territory','abbr','name_zh','name_en','name_pt','email','phone','address','country','href','title','avatar','description','content','president','registration_code','card_style','logo','website','founded_at','status'];
    protected $casts=['status'=>'boolean'];
    protected $appends = ['organizer_member_ids'];
    
    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Manipulations::FIT_CROP, 300, 300)
            ->nonQueued();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->useDisk('form');
    }

    public function ownedBy($user=null){
        return in_array($user->id,$this->users()->get()->pluck('id')->toArray());
    }

    public function users(){
        return $this->belongsToMany(User::class);
    }

    public function members(){
        return $this->hasMany(Member::class)->with('user');
    }

    public function hasUser($user){
        return in_array($user->id,$this->users()->get()->pluck('id')->toArray());
    }

    public function approbates(){
        return $this->hasMany(Approbate::class);
    }

    public function certificates(){
        return $this->hasMany(Certificate::class)->with('media');
    }
    public function events(){
        return $this->hasMany(Event::class);
    }
    public function forms($published=null){
        if($published==null){
            return $this->hasMany(Form::class)->with('media')->withCount('entries');    
        }else{
            return $this->hasMany(Form::class)->where('published',$published)->with('media')->withCount('entries');
        }
    }
    public function features(){
        return $this->hasMany(Feature::class);
    }
    public function emails(){
        return $this->hasMany(Email::class);
    }
    public function articles(){
        return $this->hasMany(Article::class);
    }

    public function getOrganizersAttribute()
    {
        // 確保 members 已經被載入
        if (!$this->relationLoaded('members')) {
            return collect();
        }
        
        return $this->members->where('is_organizer', 1);
    }

    public function getOrganizerMemberIdsAttribute()
    {
        return $this->organizers->pluck('id')->toArray();
    }
}
