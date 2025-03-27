<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;

class UploaderController extends Controller
{
    public function image(Request $request){
        $article=new Article();
        $article->id=0;
        $article->exists=true;
        //$image=$article->addMediaFromRequest('upload')->toMediaCollection('article','article');
        $image=session('organization')->addMediaFromRequest('upload')->toMediaCollection('article','media');
        return response()->json(['url'=>$image->getUrl()]);
    }
}
