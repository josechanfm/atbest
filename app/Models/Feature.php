<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;
    protected $fillable=['organization_id','sequence','title_zh','content_zh','tags_zh','title_en','content_en','tags_en','title_pt','content_pt','tags_pt','url','image'];
    protected $appends=['organization_abbr'];
    protected $casts=['tags_zh'=>'json','tags_en'=>'json','tags_pt'=>'json'];

    public function getOrganizationAbbrAttribute(){
        return $this->organization?$this->organization->abbr:NULL;

    }

    public function organization(){
        return $this->belongsTo(Organization::class);
    }
}
