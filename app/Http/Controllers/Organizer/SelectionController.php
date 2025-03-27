<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\AdminUser;
use Inertia\Inertia;

class SelectionController extends Controller
{
    public function index(){
        $organizations=AdminUser::find(Auth()->user()->id)->organizations;
        if($organizations->count()==1){
            return redirect()->route('organizations.show',$organizations[0]->id);
        }elseif($organizations->count()>1){
            return Inertia::render('Organizer/Selection',[
                'organizations' => $organizations
            ]);
        }else{
            echo '!!';
        }
    }
}
