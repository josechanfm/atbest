<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Member;
use App\Models\Organization;
use App;

class DashboardController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(Organization::class);
    }

    public function index(){
        if(empty(session('organization'))){
            session(['organization'=>auth()->user()->member->organization]);
        }
        //dd(session('organization'),auth()->user()->organizations());
        //$organization=auth()->user()->member->organization;
        $this->authorize('view',session('organization'));
        $members=auth()->user()->members()->get();
        // dd(auth()->user(), $members, auth()->user()->organizations()->get());
        return Inertia::render('Organizer/Dashboard',[
            'organizations' => auth()->user()->organizations()->get(),
            'members'=>auth()->user()->members()->get(),
            // 'member'=>auth()->user()->member,
        ]);
    }
    
}
