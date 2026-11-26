<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Incident extends Model
{
    use HasFactory;
    protected $fillable=['topic','title','content','solution','raised_at','solved_at','user_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

}
