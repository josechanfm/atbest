<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use Illuminate\Support\Str;

class Event extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    protected $fillable=['organization_id','category_code','credit','title','valid_at','expire_at','tags','content','remark','require_login','for_member','published','with_attendance','form_id'];
    // protected $attributes=[
    //     'title_en'=>'',
    //     'category_code'=>'',
    //     'credit'=>'',
    // ];
    protected $casts=['require_login'=>'boolean','for_member'=>'boolean','published'=>'boolean','with_attendance'=>'boolean'];
    protected $appends=['form','banner_url','thumb_url'];

    public static function boot(){
        parent::boot();
        self::creating(function($model){
            $model->uuid=hash('crc32b',rand());
            $model->uuid=Str::uuid();
        });
        static::updating(function ($model){
            if(empty($model->uuid)){
                //$model->uuid=hash('crc32b',rand());
                $model->uuid=Str::uuid();
            }
        });
    }
    public function getFormAttribute(){
        return Form::where('id',$this->form_id)->select('id','uuid','title','valid_at','expire_at')->first();
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


    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function managers(){
        return $this->belongsToMany(Member::class,'event_manager','event_id','member_id');
    }

    public static function recents(){
        return self::publics();
    }
    public static function publics(){
        return Event::where('published',true)->where('for_member',false)
        ->where(function($query){
            $query->whereNull('valid_at')->orWhere('valid_at','<=',date('Y-m-d'));
        })
        ->where(function($query){
            $query->whereNull('expire_at')->orWhere('expire_at','>=',date('Y-m-d'));
        })
        ->orderBy('created_at','DESC')->get();
    }

    public static function privites(){
        if(empty(session('organization'))){
            return false;
        }
        return Event::where('published',true)->where('organization_id',session('organization')->id)
                ->where(function($query){
                    $query->whereNull('valid_at')->orWhere('valid_at','<=',date('Y-m-d'));
                })
                ->where(function($query){
                    $query->whereNull('expire_at')->orWhere('expire_at','>=',date('Y-m-d'));
                })
                ->get();

    }


}
