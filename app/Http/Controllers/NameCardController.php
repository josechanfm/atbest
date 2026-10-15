<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class NameCardController extends Controller
{
    public function show(Request $request)
    {
        $member = auth()->user()->member; 
        $nameCard = $member?->nameCard ?? null;
        
        return Inertia::render('NameCard', [
            'member' => $member->load('organization', 'nameCard'),
            'nameCard' => $nameCard,
        ]);
    }
}
