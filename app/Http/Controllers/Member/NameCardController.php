<?php
namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;

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
