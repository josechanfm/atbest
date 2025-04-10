<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Article;

class ArticleController extends Controller
{
    public function item(Request $request)
    {
        //dd(Article::where('uuid',$request->t)->where('published',true)->first());
        if(empty($request->t)){
            // return to_route('/');
            return response()->redirectTo("/");
        }else if(session('member') || (auth()->user() && auth()->user()->hasRole('admin'))){
            $article=Article::where('uuid',$request->t)->where('published',true)->first();
        }else{
            $article=Article::where('uuid',$request->t)->where('published',true)->where('for_member',false)->first();
        }
        return Inertia::render('Article',[
            'article'=>$article
        ]);
    }
}
