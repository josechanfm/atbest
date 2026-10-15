<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Member;

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
        Member::where('user_id',$member->user->id)->update(['is_default'=>false]);
        // dd($member);
        Member::where('id',$member->id)->update(['is_default'=>true]);
        // $member->is_default=true;
        // $member->save();
        //dd($member->toArray());
        //$member->organization;
        
        return to_route('member.dashboard');
    }
    

}
