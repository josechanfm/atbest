<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Str;
  
class Article extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable=['uuid','organization_id','sequence','category_code','intro','title','content','tags','valid_at','expire_at','url','reference','published','for_member','author','thumbnail','lang','user_id'];
    protected $casts=['tags'=>'json','published'=>'boolean','for_member'=>'boolean'];
    protected $appends=['organization_abbr','banner_url','thumb_url'];

    public static function boot(){
        parent::boot();
        self::creating(function($model){
            $model->uuid=Str::uuid();            
        });
        static::created(function ($model){
            $model->sequence=$model->id;
        });
        static::updating(function ($model){
            if(empty($model->uuid)){
                $model->uuid=Str::uuid();
            }
        });
    }
    public function getOrganizationAbbrAttribute(){
        return $this->organization?$this->organization->abbr:NULL;
    }
    public function getBannerUrlAttribute(){
        return $this->getFirstMediaUrl('banner');
    }
    public function getThumbUrlAttribute(){
        return $this->getFirstMediaUrl('thumb');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Manipulations::FIT_CROP, 300, 300)
            ->nonQueued();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner')->singleFile()->useDisk('media');
        $this->addMediaCollection('thumb')->singleFile()->useDisk('media');
    }

    public static function recents(){
        return self::publics();
    }
    public static function publics(){
        return Article::where('published',true)->where('for_member',false)
                ->where(function($query){
                    $query->whereNull('valid_at')->orWhere('valid_at','<=',date('Y-m-d'));
                })
                ->where(function($query){
                    $query->whereNull('expire_at')->orWhere('expire_at','>=',date('Y-m-d'));
                })
                ->orderBy('sequence','DESC')->get();
    }
    public static function privates(){
        if(empty(session('organization'))){
            return false;
        }
        return Article::where('published',true)->where('for_member',true)->where('organization_id',session('organization')->id)
                ->where(function($query){
                    $query->whereNull('valid_at')->orWhere('valid_at','<=',date('Y-m-d'));
                })
                ->where(function($query){
                    $query->whereNull('expire_at')->orWhere('expire_at','>=',date('Y-m-d'));
                })
                ->get();
    }
    public function organization(){
        return $this->belongsTo(Organization::class);
    }
}
