<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Models\Member;
use Inertia\Inertia;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // dd(Auth()->user()->member);
        // $member= Member::where('user_id',auth()->user()->id)->with('guardian')->first();
        session_unset();
        $members=auth()->user()->members;
        if($request->service && $request->service=='widget'){
            return redirect()->route('widget.dashboard');
        }

        if(auth()->user()->hasRole('admin')){
            return redirect()->route('admin.dashboard');
        }
        auth()->user()->permissions;
        if($members){
            return redirect()->route('member.dashboard');
        }else{
            return Inertia::render('Error',[
                'message'=>"You don't belongs to any organization"
            ]);
        }

        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended(config('fortify.home'));
    }
}