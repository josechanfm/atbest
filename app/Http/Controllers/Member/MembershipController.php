<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class MembershipController extends Controller
{
    public function index(){
        // $member=auth()->user()->member;
        $certificates=Auth()->user()->member->certificates;
        return Inertia::render('Member/Membership',[
            // 'member'=>$member,
            'certificates'=>$certificates
        ]);
    }
    public function cards(){
        $members=auth()->user()->members;
        //dd($member);
        return Inertia::render('Member/MembershipCards',[
            'members'=>$members
        ]);
    }
    public function switch(Member $member){
        abort_unless($member->user_id === auth()->id(), 403);

        DB::transaction(function () use ($member) {
            Member::where('user_id', $member->user_id)->update(['is_default' => false]);
            Member::whereKey($member->id)->update(['is_default' => true]);
        });

        $member->refresh();
        session([
            'member' => $member,
            'organization' => $member->organization,
        ]);

        return to_route('member.dashboard');
    }
    

}
