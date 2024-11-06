<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebsiteController extends Controller
{
    private $organization;

    public function __construct(Request $request){
        $subdomain = $request->route()->parameter('abbr');
        $this->organization = Organization::where('subdomain', $subdomain)->first();
        
    }
    
    public function home(){
        //dd($this->organization,$this->organization->articles()->orderBy('created_at')->limit(5)->get());
        return Inertia::render('Website/Home',[
            'organization'=>$this->organization,
            'articles'=>$this->organization->articles()->orderBy('created_at')->limit(5)->get()
        ]);
    }
    public function board(){
        dd('board');
    }
    public function former(){
        dd('former');
    }
    public function article(){
        return Inertia::render('Website/Home',[
            'articles'=>$this->organization->articles()->ordered()->get()
        ]);
        dd('article',$this->organization);
    }
    public function forms(){
        dd('forms');
    }
    public function newsEvents(){
        dd('news & events');
    }
}
