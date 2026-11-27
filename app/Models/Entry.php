<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Entry extends Model
{
    use HasFactory;
    
    protected $appends=['uid','submitted_at'];

    public function getSubmittedAtAttribute(){
        return date_format($this->created_at, "Y-m-d H:i");
    }

    public function getUidAttribute(){
        $form=Form::find($this->form_id);
        return substr('00'.strtoupper(dechex($form->organization_id)),-2).'-'.substr('0000'.strtoupper(dechex($this->id)),-5);
    }
    public function records(){
        return $this->hasMany(EntryRecord::class);
    }
    public function member(){
        return $this->belongsTo(Member::class);
    }
    public function form(){
        return $this->belongsTo(Form::class)->with('fields');
    }
    
    public function firstRecord()
    {
        return $this->hasOne(EntryRecord::class)->oldestOfMany();
        // Or ->latestOfMany() for the latest record
    }
}
