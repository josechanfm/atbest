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
        // dd(session('organization'),auth()->user()->organizations());
        //$organization=auth()->user()->member->organization;
        $this->authorize('view',session('organization'));
        return Inertia::render('Organizer/Dashboard',[
            'organizations' => auth()->user()->organizations(),
            // 'member'=>auth()->user()->member,
        ]);
    }
    
}
